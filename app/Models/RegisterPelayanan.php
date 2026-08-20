<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'surat_id', 'penduduk_id', 'petugas_id',
    'nomor_register', 'jenis_pelayanan',
    'pedukuhan_pemohon', 'tanggal_pelayanan', 'keterangan',
])]
class RegisterPelayanan extends Model
{
    protected $table = 'register_pelayanan';

    protected function casts(): array
    {
        return [
            'tanggal_pelayanan' => 'date',
        ];
    }

    /**
     * Harus dipanggil di dalam DB::transaction() — lihat catatan di JenisSurat::generateNomorSurat().
     *
     * Berbasis MAX nomor urut yang sudah ada (bukan COUNT baris), supaya tidak menghasilkan
     * nomor yang sudah dipakai kalau ada entri register yang dihapus (lihat
     * RegisterController::destroy()) — COUNT baris akan berkurang saat sebuah baris dihapus,
     * padahal nomor tertinggi yang pernah terbit tidak berubah.
     */
    public static function generateNomorRegister(): string
    {
        $tahun  = now()->year;
        $prefix = "REG-{$tahun}-";

        $terakhir = static::where('nomor_register', 'like', "{$prefix}%")
                           ->lockForUpdate()
                           ->orderByDesc('nomor_register')
                           ->value('nomor_register');

        $urutanTerakhir = $terakhir ? (int) substr($terakhir, strlen($prefix)) : 0;
        $urutan         = str_pad($urutanTerakhir + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$urutan}";
    }

    public function scopeTahunIni(Builder $query): Builder
    {
        return $query->whereYear('tanggal_pelayanan', now()->year);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}