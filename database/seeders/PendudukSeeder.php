<?php

namespace Database\Seeders;

use App\Imports\PendudukImport;
use App\Models\Penduduk;
use Illuminate\Database\Seeder;

class PendudukSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/seeder/penduduk.csv');

        if (! file_exists($filePath)) {
            $this->command->warn('File tidak ditemukan: ' . $filePath);
            return;
        }

        $this->command->info('Mengimport data penduduk dari CSV...');

        $imported = (new PendudukImport)->import($filePath);

        $this->command->info("Selesai! Total diimport: {$imported}");
        $this->command->info("Total di database: " . Penduduk::count());
    }
}