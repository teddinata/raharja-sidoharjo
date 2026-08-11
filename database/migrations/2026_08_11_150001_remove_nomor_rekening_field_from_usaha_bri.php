<?php

use App\Models\JenisSurat;
use Illuminate\Database\Migrations\Migration;

/**
 * Hapus field "Nomor Rekening BRI" dari Surat Keterangan Usaha BRI —
 * digantikan sepenuhnya oleh field "Kantor / Unit BRI". Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        $jenis = JenisSurat::where('kode', 'USAHA_BRI')->first();
        if (! $jenis) {
            return;
        }

        $fields = $jenis->fields_tambahan ?? [];
        $jenis->fields_tambahan = array_values(array_filter(
            $fields,
            fn ($f) => $f['key'] !== 'nomor_rekening'
        ));
        $jenis->save();
    }

    public function down(): void
    {
        $jenis = JenisSurat::where('kode', 'USAHA_BRI')->first();
        if (! $jenis) {
            return;
        }

        $fields = $jenis->fields_tambahan ?? [];
        $keys = array_column($fields, 'key');
        if (in_array('nomor_rekening', $keys, true)) {
            return;
        }

        $result = [];
        foreach ($fields as $field) {
            $result[] = $field;
            if ($field['key'] === 'alamat_usaha') {
                $result[] = ['key' => 'nomor_rekening', 'type' => 'text', 'label' => 'Nomor Rekening BRI', 'required' => false];
            }
        }
        $jenis->fields_tambahan = $result;
        $jenis->save();
    }
};
