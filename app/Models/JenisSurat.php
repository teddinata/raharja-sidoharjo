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
     */
    public function generateNomorSurat(): string
    {
        $tahun  = now()->year;
        $jumlah = $this->surat()
                       ->where('status', 'terbit')
                       ->whereYear('created_at', $tahun)
                       ->lockForUpdate()
                       ->count();

        $urutan = str_pad($jumlah + 1, 3, '0', STR_PAD_LEFT);

        return "{$this->nomor_format}/{$urutan}/{$tahun}";
    }

    public function surat()
    {
        return $this->hasMany(Surat::class);
    }
}