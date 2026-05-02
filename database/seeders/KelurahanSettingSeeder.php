<?php

namespace Database\Seeders;

use App\Models\KelurahanSetting;
use Illuminate\Database\Seeder;

class KelurahanSettingSeeder extends Seeder
{
    public function run(): void
    {
        KelurahanSetting::updateOrCreate(
            ['id' => 1],
            [
                'nama_kelurahan' => 'Sidoharjo',
                'nama_kapanewon' => 'Samigaluh',
                'nama_kabupaten' => 'Kulon Progo',
                'nama_provinsi'  => 'Daerah Istimewa Yogyakarta',
                'nama_lurah'     => null,
                'nip_lurah'      => null,
                'alamat'         => null,
                'kode_pos'       => '55673',
                'telepon'        => null,
                'email'          => null,
                'website'        => null,
                'logo_path'      => null,
                'ttd_lurah_path' => null,
                'updated_at'     => now(),
            ]
        );
    }
}