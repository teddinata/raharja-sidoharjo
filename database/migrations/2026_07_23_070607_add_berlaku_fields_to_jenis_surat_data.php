<?php

use App\Models\JenisSurat;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Tambahkan field opsional berlaku_dari/berlaku_sampai ke fields_tambahan
     * setiap jenis surat, supaya form Buat Surat & Edit Surat bisa mengisinya.
     */
    public function up(): void
    {
        JenisSurat::all()->each(function (JenisSurat $jenis) {
            $fields = $jenis->fields_tambahan ?? [];
            $keys   = array_column($fields, 'key');

            if (in_array('berlaku_dari', $keys, true)) {
                return;
            }

            $fields[] = ['key' => 'berlaku_dari', 'type' => 'date', 'label' => 'Berlaku Dari', 'required' => false];
            $fields[] = ['key' => 'berlaku_sampai', 'type' => 'date', 'label' => 'Berlaku Sampai', 'required' => false];

            $jenis->fields_tambahan = $fields;
            $jenis->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        JenisSurat::all()->each(function (JenisSurat $jenis) {
            $fields = $jenis->fields_tambahan ?? [];
            $jenis->fields_tambahan = array_values(array_filter(
                $fields,
                fn ($f) => ! in_array($f['key'], ['berlaku_dari', 'berlaku_sampai'], true)
            ));
            $jenis->save();
        });
    }
};
