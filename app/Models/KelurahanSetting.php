<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nama_kelurahan', 'nama_kapanewon', 'nama_kabupaten', 'nama_provinsi',
    'nama_lurah', 'nip_lurah', 'nama_carik', 'nip_carik', 'alamat', 'kode_pos', 'telepon',
    'email', 'website', 'logo_path', 'ttd_lurah_path', 'ttd_carik_path', 'updated_at',
])]
class KelurahanSetting extends Model
{
    protected $table = 'kelurahan_settings';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'nama_kelurahan' => 'Sidoharjo',
            'nama_kapanewon' => 'Samigaluh',
            'nama_kabupaten' => 'Kulon Progo',
            'nama_provinsi'  => 'Daerah Istimewa Yogyakarta',
        ]);
    }

    public function getNamaLengkapAttribute(): string
    {
        return "Kalurahan {$this->nama_kelurahan}, Kapanewon {$this->nama_kapanewon}, Kabupaten {$this->nama_kabupaten}";
    }
}