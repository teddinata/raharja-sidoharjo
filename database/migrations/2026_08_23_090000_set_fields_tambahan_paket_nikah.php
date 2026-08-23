<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Melengkapi field NIKAH_L & NIKAH_P untuk paket surat permohonan pernikahan
 * (Model N1-N6 + surat lokal), sesuai Keputusan Dirjen Bimas Islam No. 473/2020.
 *
 * Key field pasangan yang lama (nama_calon_istri, nik_calon_istri, dst) sengaja
 * dipertahankan supaya data surat yang sudah terlanjur dibuat tidak hilang.
 *
 * Nama & NIK kedua orang tua serta nama Ketua RT tidak diminta di sini karena sudah
 * tersedia di tabel penduduk dan diisi otomatis oleh template.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->simpan('NIKAH_L', $this->fields('L'));
        $this->simpan('NIKAH_P', $this->fields('P'));
    }

    public function down(): void
    {
        // Kembali ke daftar field sebelum paket N1-N6.
        $this->simpan('NIKAH_L', $this->fieldsLama('istri'));
        $this->simpan('NIKAH_P', $this->fieldsLama('suami'));
    }

    private function simpan(string $kode, array $fields): void
    {
        DB::table('jenis_surat')
            ->where('kode', $kode)
            ->update(['fields_tambahan' => json_encode(array_values($fields))]);
    }

    private function fields(string $jk): array
    {
        $pria = $jk === 'L';
        $sfx  = $pria ? 'istri' : 'suami';
        $seb  = $pria ? 'Istri' : 'Suami';
        $bin  = $pria ? 'Binti' : 'Bin';   // sebutan untuk pasangan
        $binP = $pria ? 'Bin' : 'Binti';   // sebutan untuk pemohon

        $agama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Aliran Kepercayaan'];

        return [
            $this->f('sertakan_n5', 'Sertakan Model N5 (Surat Izin Orang Tua)', 'checkbox', 'Formulir yang Disertakan'),
            $this->f('sertakan_n6', 'Sertakan Model N6 (Ket. Kematian Suami/Istri)', 'checkbox', 'Formulir yang Disertakan'),
            $this->f('sertakan_pernyataan', 'Sertakan Surat Pernyataan Belum Pernah Menikah', 'checkbox', 'Formulir yang Disertakan',
                default: $pria ? '1' : ''),
            $this->f('sertakan_wali', 'Sertakan Surat Keterangan Wali Nikah', 'checkbox', 'Formulir yang Disertakan',
                default: $pria ? '' : '1'),

            $this->f('bin_binti', "{$binP} Pemohon (nama ayah)", 'text', 'Data Pemohon'),
            $this->f('kewarganegaraan', 'Kewarganegaraan', 'text', 'Data Pemohon', placeholder: 'Indonesia'),
            $this->f('status_kawin_detail', 'Status Perkawinan', 'select', 'Data Pemohon',
                options: $pria ? ['Perjaka', 'Duda'] : ['Perawan', 'Janda']),
            $this->f('nama_pasangan_terdahulu', "Nama {$seb} Terdahulu", 'text', 'Data Pemohon'),

            $this->f("nama_calon_{$sfx}", "Nama Calon {$seb}", 'text', "Data Calon {$seb}", required: true),
            $this->f("bin_binti_calon_{$sfx}", "{$bin} Calon {$seb}", 'text', "Data Calon {$seb}"),
            $this->f("nik_calon_{$sfx}", "NIK Calon {$seb}", 'text', "Data Calon {$seb}"),
            $this->f("tempat_lahir_{$sfx}", "Tempat Lahir Calon {$seb}", 'text', "Data Calon {$seb}"),
            $this->f("tgl_lahir_{$sfx}", "Tanggal Lahir Calon {$seb}", 'date', "Data Calon {$seb}"),
            $this->f("kewarganegaraan_{$sfx}", "Kewarganegaraan Calon {$seb}", 'text', "Data Calon {$seb}", placeholder: 'Indonesia'),
            $this->f("agama_{$sfx}", "Agama Calon {$seb}", 'select', "Data Calon {$seb}", options: $agama),
            $this->f("pekerjaan_{$sfx}", "Pekerjaan Calon {$seb}", 'text', "Data Calon {$seb}"),
            $this->f("alamat_{$sfx}", "Alamat Calon {$seb}", 'text', "Data Calon {$seb}"),

            $this->f('ayah_bin', 'Bin Ayah (nama kakek)', 'text', 'Data Ayah'),
            $this->f('ayah_tempat_lahir', 'Tempat Lahir Ayah', 'text', 'Data Ayah'),
            $this->f('ayah_tgl_lahir', 'Tanggal Lahir Ayah', 'date', 'Data Ayah'),
            $this->f('ayah_kewarganegaraan', 'Kewarganegaraan Ayah', 'text', 'Data Ayah', placeholder: 'Indonesia'),
            $this->f('ayah_agama', 'Agama Ayah', 'select', 'Data Ayah', options: $agama),
            $this->f('ayah_pekerjaan', 'Pekerjaan Ayah', 'text', 'Data Ayah'),
            $this->f('ayah_alamat', 'Alamat Ayah', 'text', 'Data Ayah'),

            $this->f('ibu_binti', 'Binti Ibu (nama kakek)', 'text', 'Data Ibu'),
            $this->f('ibu_tempat_lahir', 'Tempat Lahir Ibu', 'text', 'Data Ibu'),
            $this->f('ibu_tgl_lahir', 'Tanggal Lahir Ibu', 'date', 'Data Ibu'),
            $this->f('ibu_kewarganegaraan', 'Kewarganegaraan Ibu', 'text', 'Data Ibu', placeholder: 'Indonesia'),
            $this->f('ibu_agama', 'Agama Ibu', 'select', 'Data Ibu', options: $agama),
            $this->f('ibu_pekerjaan', 'Pekerjaan Ibu', 'text', 'Data Ibu'),
            $this->f('ibu_alamat', 'Alamat Ibu', 'text', 'Data Ibu'),

            $this->f('kua_tujuan', 'KUA Kapanewon Tujuan', 'text', 'Rencana Akad Nikah'),
            $this->f('hari_akad', 'Hari Akad', 'text', 'Rencana Akad Nikah', placeholder: 'Kamis'),
            $this->f('tanggal_akad', 'Tanggal Akad', 'date', 'Rencana Akad Nikah'),
            $this->f('jam_akad', 'Jam Akad', 'text', 'Rencana Akad Nikah', placeholder: '09.00'),
            $this->f('tempat_akad', 'Tempat Akad Nikah', 'text', 'Rencana Akad Nikah'),

            $this->f('pergi_ke', 'Pergi ke', 'text', 'Surat Keterangan Kalurahan'),
            $this->f('pengikut', 'Pengikut', 'text', 'Surat Keterangan Kalurahan'),
            $this->f('keperluan', 'Keperluan', 'text', 'Surat Keterangan Kalurahan'),
            $this->f('adat_istiadat', 'Adat Istiadat', 'text', 'Surat Keterangan Kalurahan', placeholder: 'Baik'),
            $this->f('keterangan_lain', 'Keterangan', 'text', 'Surat Keterangan Kalurahan'),
            $this->f('berlaku_sampai_teks', 'Berlaku Sampai', 'text', 'Surat Keterangan Kalurahan', placeholder: 'Pelaksanaan Nikah'),
            $this->f('saksi_dukuh', 'Nama Dukuh (saksi)', 'text', 'Surat Keterangan Kalurahan'),
            $this->f('pergi_ke_2', 'Pergi ke (surat keterangan ke-2)', 'text', 'Surat Keterangan Kalurahan',
                placeholder: $pria ? 'KUA setempat' : 'Puskesmas setempat'),
            $this->f('keperluan_2', 'Keperluan (surat keterangan ke-2)', 'text', 'Surat Keterangan Kalurahan',
                placeholder: $pria ? 'Daftar Nikah' : 'Imunisasi TT'),

            $this->f('almarhum_nama', "Nama Almarhum/ah {$seb}", 'text', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_bin_binti', "{$bin} Almarhum/ah", 'text', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_nik', 'NIK Almarhum/ah', 'text', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_tempat_lahir', 'Tempat Lahir Almarhum/ah', 'text', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_tgl_lahir', 'Tanggal Lahir Almarhum/ah', 'date', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_agama', 'Agama Almarhum/ah', 'select', 'Data Almarhum (Model N6)', options: $agama),
            $this->f('almarhum_pekerjaan', 'Pekerjaan Almarhum/ah', 'text', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_alamat', 'Alamat Almarhum/ah', 'text', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_tgl_meninggal', 'Tanggal Meninggal', 'date', 'Data Almarhum (Model N6)'),
            $this->f('almarhum_tempat_meninggal', 'Tempat Meninggal', 'text', 'Data Almarhum (Model N6)'),

            $this->f('wali_nama', 'Nama Wali Nikah', 'text', 'Data Wali Nikah'),
            $this->f('wali_tempat_lahir', 'Tempat Lahir Wali', 'text', 'Data Wali Nikah'),
            $this->f('wali_tgl_lahir', 'Tanggal Lahir Wali', 'date', 'Data Wali Nikah'),
            $this->f('wali_agama', 'Agama Wali', 'select', 'Data Wali Nikah', options: $agama),
            $this->f('wali_hubungan_nasab', 'Hubungan Nasab', 'text', 'Data Wali Nikah', placeholder: 'Ayah kandung'),
            $this->f('wali_sebab', 'Sebab', 'text', 'Data Wali Nikah'),
        ];
    }

    private function fieldsLama(string $sfx): array
    {
        $seb = ucfirst($sfx);

        return [
            ['key' => "nama_calon_{$sfx}", 'type' => 'text', 'label' => "Nama Calon {$seb}", 'required' => true],
            ['key' => "nik_calon_{$sfx}", 'type' => 'text', 'label' => "NIK Calon {$seb}", 'required' => false],
            ['key' => "tempat_lahir_{$sfx}", 'type' => 'text', 'label' => "Tempat Lahir {$seb}", 'required' => false],
            ['key' => "tgl_lahir_{$sfx}", 'type' => 'date', 'label' => "Tgl Lahir {$seb}", 'required' => false],
            ['key' => "pekerjaan_{$sfx}", 'type' => 'text', 'label' => "Pekerjaan {$seb}", 'required' => false],
            ['key' => "alamat_{$sfx}", 'type' => 'text', 'label' => "Alamat {$seb}", 'required' => false],
            ['key' => 'tanggal_akad', 'type' => 'date', 'label' => 'Rencana Tanggal Akad', 'required' => false],
            ['key' => 'berlaku_dari', 'type' => 'date', 'label' => 'Berlaku Dari', 'required' => false],
            ['key' => 'berlaku_sampai', 'type' => 'date', 'label' => 'Berlaku Sampai', 'required' => false],
        ];
    }

    private function f(
        string $key,
        string $label,
        string $type,
        string $section,
        bool $required = false,
        ?array $options = null,
        ?string $placeholder = null,
        ?string $default = null,
    ): array {
        $field = compact('key', 'label', 'type', 'required', 'section');

        if ($options !== null) {
            $field['options'] = $options;
        }
        if ($placeholder !== null) {
            $field['placeholder'] = $placeholder;
        }
        if ($default !== null) {
            $field['default'] = $default;
        }

        return $field;
    }
};
