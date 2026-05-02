<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'surat_id', 'atas_nama', 'jabatan',
    'nip', 'ttd_image_path', 'ditandatangani_at',
])]
class TtdSurat extends Model
{
    protected $table = 'ttd_surat';

    protected function casts(): array
    {
        return [
            'ditandatangani_at' => 'datetime',
        ];
    }

    public static function dariSettingLurah(int $suratId): static
    {
        $setting = KelurahanSetting::instance();

        return static::create([
            'surat_id'          => $suratId,
            'atas_nama'         => $setting->nama_lurah,
            'jabatan'           => "Lurah {$setting->nama_kelurahan}",
            'nip'               => $setting->nip_lurah,
            'ttd_image_path'    => $setting->ttd_lurah_path,
            'ditandatangani_at' => now(),
        ]);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}