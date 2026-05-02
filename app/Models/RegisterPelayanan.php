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

    public static function generateNomorRegister(): string
    {
        $tahun  = now()->year;
        $jumlah = static::whereYear('tanggal_pelayanan', $tahun)->count();
        $urutan = str_pad($jumlah + 1, 3, '0', STR_PAD_LEFT);

        return "REG-{$tahun}-{$urutan}";
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