<?php

namespace App\Support;

use App\Models\KelurahanSetting;
use App\Models\Surat;
use Carbon\Carbon;

/**
 * Menyiapkan data untuk paket surat permohonan pernikahan (Model N1-N6 + surat lokal).
 *
 * Sebagian besar isian diambil otomatis dari data penduduk — termasuk nama & NIK kedua
 * orang tua serta nama Ketua RT — sisanya dari data_tambahan yang diisi petugas.
 */
class DataSuratNikah
{
    /**
     * @param  string  $jk  'L' bila pemohon calon suami, 'P' bila calon istri
     * @return array<string, mixed> variabel siap pakai untuk view formulir
     */
    public static function untuk(Surat $surat, KelurahanSetting $setting, string $jk): array
    {
        $p    = $surat->penduduk;
        $e    = $surat->data_tambahan ?? [];
        $ttd  = $surat->ttd;
        $pria = $jk === 'L';

        // Nama key pasangan mengikuti yang sudah dipakai sejak awal supaya data surat
        // lama tidak hilang: "..._istri" untuk NIKAH_L, "..._suami" untuk NIKAH_P.
        $sfx = $pria ? 'istri' : 'suami';

        $wilayah       = "{$setting->nama_kelurahan} {$setting->nama_kapanewon} {$setting->nama_kabupaten}";
        $alamatPemohon = trim(($p->pedukuhan ?: '-') . ', ' . $wilayah, ', ');

        $pemohon = [
            'nama'            => $p->nama_lengkap,
            'bin'             => self::isi($e['bin_binti'] ?? null, self::isi($p->nama_ayah)),
            'nik'             => self::isi($p->nik),
            'jenis_kelamin'   => self::isi($p->jenis_kelamin),
            'ttl'             => self::ttl($p->tempat_lahir, $p->tanggal_lahir),
            'kewarganegaraan' => self::isi($e['kewarganegaraan'] ?? null, 'Indonesia'),
            'agama'           => self::isi($p->agama),
            'pekerjaan'       => self::isi($p->pekerjaan),
            'alamat'          => $alamatPemohon,
            'status'          => self::isi($e['status_kawin_detail'] ?? null, self::isi($p->status_perkawinan)),
            'terdahulu'       => self::isi($e['nama_pasangan_terdahulu'] ?? null),
        ];

        $pasangan = [
            'nama'            => self::isi($e["nama_calon_{$sfx}"] ?? null),
            'bin'             => self::isi($e["bin_binti_calon_{$sfx}"] ?? null),
            'nik'             => self::isi($e["nik_calon_{$sfx}"] ?? null),
            'ttl'             => self::ttl($e["tempat_lahir_{$sfx}"] ?? null, $e["tgl_lahir_{$sfx}"] ?? null),
            'kewarganegaraan' => self::isi($e["kewarganegaraan_{$sfx}"] ?? null, 'Indonesia'),
            'agama'           => self::isi($e["agama_{$sfx}"] ?? null),
            'pekerjaan'       => self::isi($e["pekerjaan_{$sfx}"] ?? null),
            'alamat'          => self::isi($e["alamat_{$sfx}"] ?? null),
        ];

        $ayah = self::orangTua($e, 'ayah', self::isi($p->nama_ayah), self::isi($p->nik_ayah), $alamatPemohon);
        $ibu  = self::orangTua($e, 'ibu', self::isi($p->nama_ibu), self::isi($p->nik_ibu), $alamatPemohon);

        $almarhum = [
            'nama'             => self::isi($e['almarhum_nama'] ?? null),
            'bin'              => self::isi($e['almarhum_bin_binti'] ?? null),
            'nik'              => self::isi($e['almarhum_nik'] ?? null),
            'ttl'              => self::ttl($e['almarhum_tempat_lahir'] ?? null, $e['almarhum_tgl_lahir'] ?? null),
            'kewarganegaraan'  => self::isi($e['almarhum_kewarganegaraan'] ?? null, 'Indonesia'),
            'agama'            => self::isi($e['almarhum_agama'] ?? null),
            'pekerjaan'        => self::isi($e['almarhum_pekerjaan'] ?? null),
            'alamat'           => self::isi($e['almarhum_alamat'] ?? null),
            'tgl_meninggal'    => self::tanggal($e['almarhum_tgl_meninggal'] ?? null),
            'tempat_meninggal' => self::isi($e['almarhum_tempat_meninggal'] ?? null),
        ];

        $akad = [
            'kua'     => self::isi($e['kua_tujuan'] ?? null),
            'hari'    => self::isi($e['hari_akad'] ?? null),
            'tanggal' => self::tanggal($e['tanggal_akad'] ?? null),
            'jam'     => self::isi($e['jam_akad'] ?? null),
            'tempat'  => self::isi($e['tempat_akad'] ?? null),
        ];

        $keterangan = [
            'pergi_ke'       => self::isi($e['pergi_ke'] ?? null),
            'pengikut'       => self::isi($e['pengikut'] ?? null),
            'keperluan'      => self::isi($e['keperluan'] ?? null, 'Daftar Nikah dengan ' . $pasangan['nama']),
            'adat'           => self::isi($e['adat_istiadat'] ?? null, 'Baik'),
            'lain'           => self::isi($e['keterangan_lain'] ?? null, ''),
            'berlaku_sampai' => self::isi($e['berlaku_sampai_teks'] ?? null, 'Pelaksanaan Nikah'),
        ];

        $penanda = [
            'jabatan' => ($ttd?->jabatan) ?: 'Lurah ' . $setting->nama_kelurahan,
            'nama'    => ($ttd?->atas_nama) ?: ($setting->nama_lurah ?: '................................'),
        ];

        return [
            'surat'      => $surat,
            'setting'    => $setting,
            'p'          => $p,
            'e'          => $e,
            'pria'       => $pria,
            'pemohon'    => $pemohon,
            'pasangan'   => $pasangan,
            'ayah'       => $ayah,
            'ibu'        => $ibu,
            'almarhum'   => $almarhum,
            'akad'       => $akad,
            'keterangan' => $keterangan,
            'penanda'    => $penanda,
            'tglSurat'   => now()->format('d-m-Y'),
        ];
    }

    /** Apakah model opsional (N5/N6) dicentang petugas. */
    public static function dicentang(Surat $surat, string $key): bool
    {
        return filter_var(($surat->data_tambahan ?? [])[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private static function orangTua(array $e, string $prefix, string $nama, string $nik, string $alamatBawaan): array
    {
        return [
            'nama'            => $nama,
            'bin'             => self::isi($e["{$prefix}_" . ($prefix === 'ayah' ? 'bin' : 'binti')] ?? null),
            'nik'             => $nik,
            'ttl'             => self::ttl($e["{$prefix}_tempat_lahir"] ?? null, $e["{$prefix}_tgl_lahir"] ?? null),
            'kewarganegaraan' => self::isi($e["{$prefix}_kewarganegaraan"] ?? null, 'Indonesia'),
            'agama'           => self::isi($e["{$prefix}_agama"] ?? null),
            'pekerjaan'       => self::isi($e["{$prefix}_pekerjaan"] ?? null),
            'alamat'          => self::isi($e["{$prefix}_alamat"] ?? null, $alamatBawaan),
        ];
    }

    private static function isi(mixed $nilai, string $kosong = '-'): string
    {
        return filled($nilai) ? (string) $nilai : $kosong;
    }

    private static function tanggal(mixed $nilai): string
    {
        if (blank($nilai)) {
            return '-';
        }

        try {
            return Carbon::parse($nilai)->format('d-m-Y');
        } catch (\Throwable) {
            return (string) $nilai;
        }
    }

    private static function ttl(mixed $tempat, mixed $tgl): string
    {
        if (blank($tempat) && blank($tgl)) {
            return '-';
        }

        return trim(self::isi($tempat) . ', ' . self::tanggal($tgl), ', ');
    }
}
