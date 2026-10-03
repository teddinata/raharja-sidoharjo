<?php

use App\Support\WilayahRtRw;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memindahkan nama Ketua RT & Ketua RW dari tiap baris penduduk ke tabel master.
 *
 * Sebelumnya nama ketua disimpan berulang di setiap warga, jadi mengganti ketua
 * berarti mengedit ratusan warga satu per satu — dan sering tertinggal sehingga
 * satu RT punya dua nama ketua berbeda. Dengan tabel master, cukup diubah sekali.
 *
 * Nomor RT & RW di kalurahan ini unik se-kalurahan (RW 1-39, RT 1-84), jadi
 * warga dicocokkan ke master lewat kolom rt & rw yang sudah ada.
 *
 * Bila satu RT/RW punya beberapa nama ketua, yang dipakai adalah nama yang paling
 * banyak muncul. Nilai sisa error spreadsheet ("False", "#REF!") diabaikan.
 */
return new class extends Migration
{
    private const NILAI_RUSAK = ['', 'false', '#ref!', '-'];

    public function up(): void
    {
        Schema::create('rukun_warga', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 5)->unique();
            $table->string('pedukuhan', 50)->nullable();
            $table->string('nama_ketua', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('rukun_tetangga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rukun_warga_id')->constrained('rukun_warga')->cascadeOnDelete();
            $table->string('nomor', 5);
            $table->string('nama_ketua', 100)->nullable();
            $table->timestamps();

            $table->unique(['rukun_warga_id', 'nomor']);
        });

        $this->pindahkanDariPenduduk();

        Schema::table('penduduk', function (Blueprint $table) {
            $table->dropColumn(['nama_ketua_rt', 'nama_ketua_rw']);
        });
    }

    public function down(): void
    {
        Schema::table('penduduk', function (Blueprint $table) {
            $table->string('nama_ketua_rt')->nullable();
            $table->string('nama_ketua_rw')->nullable();
        });

        foreach (DB::table('rukun_tetangga as rt')
            ->join('rukun_warga as rw', 'rw.id', '=', 'rt.rukun_warga_id')
            ->get(['rw.nomor as rw', 'rt.nomor as rt', 'rt.nama_ketua as ketua_rt', 'rw.nama_ketua as ketua_rw']) as $r) {
            DB::table('penduduk')
                ->where('rw', $r->rw)
                ->where('rt', $r->rt)
                ->update(['nama_ketua_rt' => $r->ketua_rt, 'nama_ketua_rw' => $r->ketua_rw]);
        }

        Schema::dropIfExists('rukun_tetangga');
        Schema::dropIfExists('rukun_warga');
    }

    private function pindahkanDariPenduduk(): void
    {
        $warga = DB::table('penduduk')->get(['pedukuhan', 'rt', 'rw', 'nama_ketua_rt', 'nama_ketua_rw']);

        $perRw = [];
        $perRt = [];

        foreach ($warga as $w) {
            $rw = WilayahRtRw::nomor($w->rw);
            $rt = WilayahRtRw::nomor($w->rt);

            if ($rw === null) {
                continue;
            }

            $perRw[$rw]['pedukuhan'][] = $w->pedukuhan;
            $perRw[$rw]['ketua'][]     = $w->nama_ketua_rw;

            if ($rt !== null) {
                $perRt[$rw][$rt][] = $w->nama_ketua_rt;
            }
        }

        $sekarang = now();

        foreach ($perRw as $rw => $data) {
            $rwId = DB::table('rukun_warga')->insertGetId([
                'nomor'      => $rw,
                'pedukuhan'  => $this->terbanyak($data['pedukuhan']),
                'nama_ketua' => $this->terbanyak($data['ketua']),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            foreach ($perRt[$rw] ?? [] as $rt => $ketua) {
                DB::table('rukun_tetangga')->insert([
                    'rukun_warga_id' => $rwId,
                    'nomor'          => $rt,
                    'nama_ketua'     => $this->terbanyak($ketua),
                    'created_at'     => $sekarang,
                    'updated_at'     => $sekarang,
                ]);
            }
        }
    }

    /** Nilai yang paling sering muncul, mengabaikan isian kosong/rusak. */
    private function terbanyak(array $nilai): ?string
    {
        $hitung = [];

        foreach ($nilai as $n) {
            $n = trim((string) $n);

            if (in_array(strtolower($n), self::NILAI_RUSAK, true)) {
                continue;
            }

            $hitung[$n] = ($hitung[$n] ?? 0) + 1;
        }

        if ($hitung === []) {
            return null;
        }

        arsort($hitung);

        return (string) array_key_first($hitung);
    }
};
