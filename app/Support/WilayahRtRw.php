<?php

namespace App\Support;

use App\Models\RukunTetangga;
use App\Models\RukunWarga;
use Illuminate\Support\Facades\DB;

/**
 * Pencocokan warga ke master RT/RW untuk mendapatkan nama Ketua RT & Ketua RW.
 *
 * Warga tidak menyimpan id master, melainkan nomor rt & rw seperti di data
 * kependudukan. Jumlah RT/RW hanya ratusan baris, jadi seluruhnya dimuat sekali
 * ke memori dan dipakai ulang — daftar penduduk tidak perlu query per baris.
 */
class WilayahRtRw
{
    /** @var array{rw: array<string, string|null>, rt: array<string, string|null>}|null */
    private static ?array $peta = null;

    /**
     * Nomor RT/RW yang dinormalkan ("01" → "1"), atau null bila bukan angka —
     * mis. sisa error spreadsheet seperti "#REF!".
     */
    public static function nomor(mixed $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '' || ! ctype_digit($nilai)) {
            return null;
        }

        return ltrim($nilai, '0') ?: '0';
    }

    public static function ketuaRw(mixed $rw): ?string
    {
        $rw = self::nomor($rw);

        return $rw === null ? null : (self::peta()['rw'][$rw] ?? null);
    }

    public static function ketuaRt(mixed $rw, mixed $rt): ?string
    {
        $rw = self::nomor($rw);
        $rt = self::nomor($rt);

        if ($rw === null || $rt === null) {
            return null;
        }

        return self::peta()['rt']["{$rw}/{$rt}"] ?? null;
    }

    /**
     * Memastikan RT/RW warga sudah ada di master, supaya RT/RW baru langsung
     * muncul di daftar Ketua RT/RW untuk diisi. Nama ketua yang sudah ada tidak
     * pernah ditimpa; $ketuaRt/$ketuaRw hanya mengisi yang masih kosong (dipakai
     * saat import).
     */
    public static function daftarkan(
        mixed $rw,
        mixed $rt,
        ?string $pedukuhan = null,
        ?string $ketuaRt = null,
        ?string $ketuaRw = null,
    ): void {
        $rw = self::nomor($rw);
        $rt = self::nomor($rt);

        if ($rw === null) {
            return;
        }

        $master = RukunWarga::firstOrNew(['nomor' => $rw]);
        $master->pedukuhan  = $master->pedukuhan ?: $pedukuhan;
        $master->nama_ketua = $master->nama_ketua ?: self::nama($ketuaRw);

        if ($master->isDirty()) {
            $master->save();
        }

        if ($rt === null) {
            return;
        }

        $masterRt = RukunTetangga::firstOrNew(['rukun_warga_id' => $master->id, 'nomor' => $rt]);
        $masterRt->nama_ketua = $masterRt->nama_ketua ?: self::nama($ketuaRt);

        if ($masterRt->isDirty()) {
            $masterRt->save();
        }
    }

    /** Nama ketua dari spreadsheet, tanpa sisa error seperti "False" atau "#REF!". */
    private static function nama(?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        return in_array(strtolower($nilai), ['', 'false', '#ref!', '-'], true) ? null : $nilai;
    }

    /** Dipanggil setiap master berubah supaya nama ketua terbaru langsung terpakai. */
    public static function lupakan(): void
    {
        self::$peta = null;
    }

    private static function peta(): array
    {
        if (self::$peta !== null) {
            return self::$peta;
        }

        $rw = RukunWarga::pluck('nama_ketua', 'nomor')->all();

        $rt = DB::table('rukun_tetangga')
            ->join('rukun_warga', 'rukun_warga.id', '=', 'rukun_tetangga.rukun_warga_id')
            ->get(['rukun_warga.nomor as rw', 'rukun_tetangga.nomor as rt', 'rukun_tetangga.nama_ketua'])
            ->mapWithKeys(fn ($r) => ["{$r->rw}/{$r->rt}" => $r->nama_ketua])
            ->all();

        return self::$peta = ['rw' => $rw, 'rt' => $rt];
    }
}
