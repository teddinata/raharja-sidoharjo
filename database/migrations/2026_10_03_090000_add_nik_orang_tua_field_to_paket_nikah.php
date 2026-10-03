<?php

use App\Models\JenisSurat;
use Illuminate\Database\Migrations\Migration;

/**
 * Menambah isian NIK Ayah & NIK Ibu pada Pengantar Perkawinan (L & P).
 *
 * Sebelumnya NIK orang tua hanya diambil dari data penduduk pemohon, sehingga
 * kalau di sana kosong petugas tidak bisa melengkapinya dari form surat. Isian
 * ini opsional: bila dikosongkan, template tetap memakai NIK dari data penduduk.
 */
return new class extends Migration
{
    private const KODE = ['NIKAH_L', 'NIKAH_P'];

    // key field baru => key field yang menjadi patokan posisinya (disisipkan sebelumnya).
    private const FIELDS = [
        'ayah_nik' => ['sebelum' => 'ayah_bin', 'label' => 'NIK Ayah', 'section' => 'Data Ayah'],
        'ibu_nik'  => ['sebelum' => 'ibu_binti', 'label' => 'NIK Ibu', 'section' => 'Data Ibu'],
    ];

    public function up(): void
    {
        $this->ubahFields(function (array $fields): array {
            $ada = array_column($fields, 'key');

            foreach (self::FIELDS as $key => $def) {
                if (in_array($key, $ada, true)) {
                    continue;
                }

                $field = [
                    'key'         => $key,
                    'label'       => $def['label'],
                    'type'        => 'text',
                    'required'    => false,
                    'section'     => $def['section'],
                    'placeholder' => 'Kosongkan untuk memakai NIK dari data penduduk',
                ];

                $posisi = array_search($def['sebelum'], array_column($fields, 'key'), true);

                if ($posisi === false) {
                    $fields[] = $field;
                } else {
                    array_splice($fields, $posisi, 0, [$field]);
                }
            }

            return $fields;
        });
    }

    public function down(): void
    {
        $this->ubahFields(fn (array $fields): array => array_values(
            array_filter($fields, fn ($f) => ! array_key_exists($f['key'] ?? '', self::FIELDS))
        ));
    }

    private function ubahFields(callable $ubah): void
    {
        foreach (self::KODE as $kode) {
            $jenis = JenisSurat::where('kode', $kode)->first();

            if (! $jenis) {
                continue;
            }

            $jenis->fields_tambahan = $ubah(array_values((array) $jenis->fields_tambahan));
            $jenis->save();
        }
    }
};
