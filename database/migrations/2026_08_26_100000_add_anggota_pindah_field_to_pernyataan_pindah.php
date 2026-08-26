<?php

use App\Models\JenisSurat;
use Illuminate\Database\Migrations\Migration;

/**
 * Menambah pilihan anggota keluarga yang ikut pindah pada Surat Pernyataan Pindah,
 * sama seperti yang sudah ada di Surat Pengantar Pindah Penduduk, supaya tabel di
 * suratnya hanya memuat anggota keluarga yang benar-benar ikut.
 */
return new class extends Migration
{
    private const KODE = 'PERNYATAAN_PINDAH';
    private const KEY  = 'anggota_pindah';

    private const FIELD = [
        'key'      => self::KEY,
        'label'    => 'Anggota Keluarga yang Ikut Pindah',
        'type'     => 'keluarga',
        'required' => false,
    ];

    public function up(): void
    {
        $this->ubahFields(function (array $fields): array {
            foreach ($fields as $f) {
                if (($f['key'] ?? null) === self::KEY) {
                    return $fields;
                }
            }

            // Paling depan supaya petugas memilih anggota lebih dulu, sebelum
            // mengisi alamat tujuan dan seterusnya.
            array_unshift($fields, self::FIELD);

            return $fields;
        });
    }

    public function down(): void
    {
        $this->ubahFields(fn (array $fields): array => array_values(
            array_filter($fields, fn ($f) => ($f['key'] ?? null) !== self::KEY)
        ));
    }

    private function ubahFields(callable $ubah): void
    {
        $jenis = JenisSurat::where('kode', self::KODE)->first();

        if (! $jenis) {
            return;
        }

        $jenis->fields_tambahan = $ubah((array) $jenis->fields_tambahan);
        $jenis->save();
    }
};
