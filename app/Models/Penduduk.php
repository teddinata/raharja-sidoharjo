<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'no_kk', 'nik', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir',
    'jenis_kelamin', 'agama', 'pendidikan', 'pekerjaan',
    'status_perkawinan', 'hub_keluarga', 'pedukuhan', 'rt', 'rw',
    'nama_ketua_rt', 'nama_ketua_rw',
    'nama_ayah', 'nik_ayah', 'nama_ibu', 'nik_ibu',
    'is_aktif',
])]
class Penduduk extends Model
{
    protected $table = 'penduduk';

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'is_aktif'      => 'boolean',
        ];
    }

    public function getUmurAttribute(): ?int
    {
        return $this->tanggal_lahir?->age;
    }

    public function getTanggalLahirFormatAttribute(): ?string
    {
        if (!$this->tanggal_lahir) return null;
        Carbon::setLocale('id');
        return $this->tanggal_lahir->translatedFormat('d F Y');
    }

    public function getBinBintiAttribute(): string
    {
        $kata = $this->jenis_kelamin === 'Laki-laki' ? 'bin' : 'binti';
        return "{$this->nama_lengkap} {$kata} " . ($this->nama_ayah ?? '-');
    }

    public function getRtFormatAttribute(): string
    {
        return str_pad($this->rt ?? '0', 3, '0', STR_PAD_LEFT);
    }

    public function getRwFormatAttribute(): string
    {
        return str_pad($this->rw ?? '0', 3, '0', STR_PAD_LEFT);
    }

    public function getAlamatLengkapAttribute(): string
    {
        return ($this->pedukuhan ?? '-') . ' RT ' . $this->rt_format . ' RW ' . $this->rw_format;
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('nik', 'like', "%{$keyword}%")
              ->orWhere('nama_lengkap', 'like', "%{$keyword}%");
        });
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Anggota satu kartu keluarga, untuk daftar "Keluarga yang Pindah".
     *
     * Yang bersangkutan selalu didahulukan, sisanya diurutkan menurut hubungan
     * keluarga (Kepala Keluarga, Istri, Anak, dst) lalu nama. Hasilnya dibatasi
     * $batas baris: formulirnya memang hanya menyediakan 5 baris, dan di data ada
     * no_kk yang dipakai ratusan orang sehingga tanpa batas surat bisa membengkak.
     */
    public function serumah(int $batas = 5): \Illuminate\Support\Collection
    {
        if (blank($this->no_kk)) {
            return collect([$this]);
        }

        $urutanHubungan = [
            'Kepala Keluarga', 'Istri', 'Isteri', 'Anak', 'Menantu',
            'Cucu', 'Orang Tua', 'Mertua', 'Keponakan', 'Famili Lain',
        ];

        return static::where('no_kk', $this->no_kk)
            ->aktif()
            ->get()
            ->sortBy(function (self $orang) use ($urutanHubungan) {
                $peringkat = array_search($orang->hub_keluarga, $urutanHubungan, true);

                return [
                    $orang->getKey() === $this->getKey() ? 0 : 1,
                    $peringkat === false ? count($urutanHubungan) : $peringkat,
                    (string) $orang->nama_lengkap,
                ];
            })
            ->take($batas)
            ->values();
    }

    public function surat()
    {
        return $this->hasMany(Surat::class);
    }

    public function registerPelayanan()
    {
        return $this->hasMany(RegisterPelayanan::class);
    }
}