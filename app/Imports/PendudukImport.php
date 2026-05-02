<?php

namespace App\Imports;

use App\Models\Penduduk;

class PendudukImport
{
    public function import(string $filePath): int
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Tidak bisa membuka file: {$filePath}");
        }

        // Skip baris header
        fgetcsv($handle);

        $imported = 0;
        $batch    = [];

        while (($row = fgetcsv($handle)) !== false) {
            $nama = trim($row[2] ?? '');
            if (empty($nama)) continue;

            $nik = trim($row[1] ?? '');
            if (empty($nik)) $nik = null;

            // Skip jika NIK sudah ada
            if ($nik && Penduduk::where('nik', $nik)->exists()) continue;

            $batch[] = [
                'no_kk'             => $this->clean($row[0]),
                'nik'               => $nik,
                'nama_lengkap'      => $nama,
                'tempat_lahir'      => $this->clean($row[3]),
                'tanggal_lahir'     => $this->clean($row[4]) ?: null,
                'jenis_kelamin'     => $this->clean($row[5]),
                'pedukuhan'         => $this->clean($row[6]),
                'rt'                => $this->clean($row[7]),
                'rw'                => $this->clean($row[8]),
                'agama'             => $this->clean($row[9]),
                'pendidikan'        => $this->clean($row[10]),
                'pekerjaan'         => $this->clean($row[11]),
                'status_perkawinan' => $this->clean($row[12]),
                'hub_keluarga'      => $this->clean($row[13]),
                'nama_ayah'         => $this->clean($row[14]),
                'nama_ibu'          => $this->clean($row[15]),
                'nama_ketua_rt'     => $this->clean($row[16]),
                'nama_ketua_rw'     => $this->clean($row[17]),
                'is_aktif'          => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // Insert per 500 baris supaya tidak habis memory
            if (count($batch) >= 500) {
                Penduduk::insertOrIgnore($batch);
                $imported += count($batch);
                $batch = [];
            }
        }

        // Insert sisa batch
        if (!empty($batch)) {
            Penduduk::insertOrIgnore($batch);
            $imported += count($batch);
        }

        fclose($handle);
        return $imported;
    }

    private function clean(?string $val): ?string
    {
        if ($val === null || trim($val) === '') return null;
        return trim($val);
    }
}