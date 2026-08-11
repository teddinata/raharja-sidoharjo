<?php

use App\Models\JenisSurat;
use Illuminate\Database\Migrations\Migration;

/**
 * Data migration: menuliskan ulang sebagai kode semua perubahan `fields_tambahan`
 * yang sebelumnya ditempelkan langsung ke database lokal lewat tinker selama sesi
 * pengembangan surat kemarin, supaya ikut ter-apply otomatis di server manapun
 * yang menjalankan `php artisan migrate`. Idempotent — aman dijalankan berkali-kali
 * atau di database yang sebagian sudah berisi field-field ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Semua jenis surat: tambahkan field "Berlaku Dari" / "Berlaku Sampai" di akhir.
        JenisSurat::all()->each(function (JenisSurat $jenis) {
            $this->appendIfMissing($jenis, [
                ['key' => 'berlaku_dari', 'type' => 'date', 'label' => 'Berlaku Dari', 'required' => false],
                ['key' => 'berlaku_sampai', 'type' => 'date', 'label' => 'Berlaku Sampai', 'required' => false],
            ]);
        });

        // 2) KEMATIAN_BARU
        $this->insertAfter('KEMATIAN_BARU', 'tanggal_meninggal', [
            ['key' => 'hari_meninggal', 'type' => 'select', 'label' => 'Hari Meninggal', 'required' => false,
                'options' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']],
            ['key' => 'jam_meninggal', 'type' => 'text', 'label' => 'Jam Meninggal', 'required' => false, 'placeholder' => 'Contoh: 14.30 WIB'],
        ]);
        $this->insertAfter('KEMATIAN_BARU', 'sebab_meninggal', [
            ['key' => 'anak_ke', 'type' => 'text', 'label' => 'Anak ke-', 'required' => false, 'placeholder' => 'Contoh: 3'],
        ]);
        $this->insertAfter('KEMATIAN_BARU', 'nama_pelapor', [
            ['key' => 'nik_pelapor', 'type' => 'text', 'label' => 'NIK Pelapor', 'required' => false],
            ['key' => 'hp_pelapor', 'type' => 'text', 'label' => 'Nomor HP Pelapor', 'required' => false],
            ['key' => 'email_pelapor', 'type' => 'text', 'label' => 'Email Pelapor', 'required' => false],
        ]);

        // 3) KEMATIAN_LAMA
        $this->insertAfter('KEMATIAN_LAMA', 'sebab_meninggal', [
            ['key' => 'nama_pelapor', 'type' => 'text', 'label' => 'Nama Pelapor', 'required' => false],
            ['key' => 'hubungan_pelapor', 'type' => 'text', 'label' => 'Hubungan Pelapor', 'required' => false],
        ]);

        // 4) USAHA_BRI
        $this->insertAfter('USAHA_BRI', 'nomor_rekening', [
            ['key' => 'kantor_unit_bri', 'type' => 'text', 'label' => 'Kantor / Unit BRI', 'required' => false],
        ]);

        // 5) KELAHIRAN_BARU
        $this->insertAfter('KELAHIRAN_BARU', 'jenis_kelamin_bayi', [
            ['key' => 'anak_ke', 'type' => 'text', 'label' => 'Anak ke-', 'required' => false, 'placeholder' => 'Contoh: 2'],
        ]);

        // 6) PENGANTAR_AKTA_LAHIR
        $this->insertAfter('PENGANTAR_AKTA_LAHIR', 'nama_bayi', [
            ['key' => 'jam_lahir_bayi', 'type' => 'text', 'label' => 'Jam Lahir', 'required' => false, 'placeholder' => 'Contoh: 14.30 WIB'],
            ['key' => 'tempat_lahir_bayi', 'type' => 'text', 'label' => 'Tempat Lahir', 'required' => false],
            ['key' => 'nama_ibu', 'type' => 'text', 'label' => 'Nama Ibu', 'required' => false],
            ['key' => 'nik_ibu', 'type' => 'text', 'label' => 'NIK Ibu', 'required' => false],
        ]);

        // 7) DOMISILI — alamat_domisili ditaruh paling awal
        $this->insertAtStart('DOMISILI', [
            ['key' => 'alamat_domisili', 'type' => 'text', 'label' => 'Alamat Domisili (tempat tinggal saat ini)', 'required' => false],
        ]);

        // 8) IJIN_KERAMAIAN
        $this->insertAfter('IJIN_KERAMAIAN', 'tempat_acara', [
            ['key' => 'batas_utara', 'type' => 'text', 'label' => 'Batas Utara', 'required' => false],
            ['key' => 'batas_timur', 'type' => 'text', 'label' => 'Batas Timur', 'required' => false],
            ['key' => 'batas_selatan', 'type' => 'text', 'label' => 'Batas Selatan', 'required' => false],
            ['key' => 'batas_barat', 'type' => 'text', 'label' => 'Batas Barat', 'required' => false],
        ]);

        // 9) PENGANTAR_CERAI
        $this->insertAfter('PENGANTAR_CERAI', 'nama_pasangan', [
            ['key' => 'tujuan', 'type' => 'text', 'label' => 'Tujuan (Pengadilan Negeri)', 'required' => true, 'placeholder' => 'Contoh: Pengadilan Negeri Wates'],
        ]);
    }

    public function down(): void
    {
        // Data migration murni penambahan field opsional — sengaja tidak di-rollback
        // otomatis supaya tidak menghapus data_tambahan surat yang sudah terisi.
    }

    private function keys(array $fields): array
    {
        return array_column($fields, 'key');
    }

    private function appendIfMissing(JenisSurat $jenis, array $newFields): void
    {
        $fields = $jenis->fields_tambahan ?? [];
        $existingKeys = $this->keys($fields);
        $changed = false;

        foreach ($newFields as $newField) {
            if (! in_array($newField['key'], $existingKeys, true)) {
                $fields[] = $newField;
                $changed = true;
            }
        }

        if ($changed) {
            $jenis->fields_tambahan = $fields;
            $jenis->save();
        }
    }

    private function insertAfter(string $kode, string $afterKey, array $newFields): void
    {
        $jenis = JenisSurat::where('kode', $kode)->first();
        if (! $jenis) {
            return;
        }

        $fields = $jenis->fields_tambahan ?? [];
        $existingKeys = $this->keys($fields);

        $newFields = array_values(array_filter(
            $newFields,
            fn ($f) => ! in_array($f['key'], $existingKeys, true)
        ));
        if (empty($newFields)) {
            return;
        }

        $result = [];
        $inserted = false;
        foreach ($fields as $field) {
            $result[] = $field;
            if ($field['key'] === $afterKey) {
                array_push($result, ...$newFields);
                $inserted = true;
            }
        }
        if (! $inserted) {
            // key acuan tidak ditemukan (mis. jenis surat lama tanpa field itu) -> tambahkan di akhir saja
            array_push($result, ...$newFields);
        }

        $jenis->fields_tambahan = $result;
        $jenis->save();
    }

    private function insertAtStart(string $kode, array $newFields): void
    {
        $jenis = JenisSurat::where('kode', $kode)->first();
        if (! $jenis) {
            return;
        }

        $fields = $jenis->fields_tambahan ?? [];
        $existingKeys = $this->keys($fields);

        $newFields = array_values(array_filter(
            $newFields,
            fn ($f) => ! in_array($f['key'], $existingKeys, true)
        ));
        if (empty($newFields)) {
            return;
        }

        $jenis->fields_tambahan = array_merge($newFields, $fields);
        $jenis->save();
    }
};
