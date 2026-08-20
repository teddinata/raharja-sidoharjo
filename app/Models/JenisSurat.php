<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'kode', 'nama', 'kategori', 'nomor_format',
    'fields_tambahan', 'melibatkan_pihak_luar',
    'template_blade', 'urutan', 'is_active',
])]
class JenisSurat extends Model
{
    protected $table = 'jenis_surat';

    protected function casts(): array
    {
        return [
            'fields_tambahan'       => 'array',
            'melibatkan_pihak_luar' => 'boolean',
            'is_active'             => 'boolean',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    /**
     * Harus dipanggil di dalam DB::transaction() — lockForUpdate() di sini hanya
     * efektif mencegah race condition (dua surat terbit bersamaan dapat nomor sama)
     * selama transaksi pembungkusnya masih terbuka.
     *
     * Berbasis MAX nomor urut yang sudah ada (bukan COUNT baris), supaya tidak
     * menghasilkan nomor yang sudah dipakai kalau ada surat yang dihapus — COUNT baris
     * akan berkurang saat sebuah baris dihapus, padahal nomor tertinggi yang pernah
     * terbit tidak berubah. Lihat catatan yang sama di RegisterPelayanan::generateNomorRegister().
     */
    public function generateNomorSurat(): string
    {
        $tahun  = now()->year;
        $prefix = "{$this->nomor_format}/";
        $suffix = "/{$tahun}";

        $terakhir = $this->surat()
                          ->where('status', 'terbit')
                          ->where('nomor_surat', 'like', "{$prefix}%{$suffix}")
                          ->lockForUpdate()
                          ->orderByDesc('nomor_surat')
                          ->value('nomor_surat');

        $urutanTerakhir = $terakhir
            ? (int) substr($terakhir, strlen($prefix), -strlen($suffix))
            : 0;
        $urutan = str_pad($urutanTerakhir + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$urutan}{$suffix}";
    }

    public function surat()
    {
        return $this->hasMany(Surat::class);
    }
}