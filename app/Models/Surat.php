<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'penduduk_id', 'jenis_surat_id', 'dibuat_oleh',
    'nomor_surat', 'data_tambahan', 'data_pihak_luar',
    'status', 'file_pdf_path', 'file_docx_path', 'dicetak_at', 'diterbitkan_at',
])]
class Surat extends Model
{
    protected $table = 'surat';

    protected function casts(): array
    {
        return [
            'data_tambahan'   => 'array',
            'data_pihak_luar' => 'array',
            'dicetak_at'      => 'datetime',
            'diterbitkan_at'  => 'datetime',
        ];
    }

    /**
     * Nomor surat & nomor register dihitung dari COUNT baris yang sudah ada + 1, jadi kalau
     * dua surat diterbitkan bersamaan bisa dapat nomor yang sama. lockForUpdate() di dalam
     * transaksi ini menyerialkan percobaan yang bertabrakan; DB::transaction() dengan
     * $attempts>1 sudah otomatis retry kalau MySQL lapor deadlock/lock-wait-timeout akibat
     * penguncian itu (perilaku bawaan Laravel). Loop luar di sini cuma jaring pengaman untuk
     * duplicate-entry (1062) — kasus yang TIDAK dianggap "concurrency error" oleh Laravel
     * sehingga tidak di-retry otomatis oleh DB::transaction().
     */
    public function terbitkan(User $petugas): void
    {
        if ($this->status !== 'draft') {
            throw new \LogicException("Surat berstatus [{$this->status}] tidak bisa diterbitkan.");
        }

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($petugas) {
                    $this->nomor_surat    = $this->jenisSurat->generateNomorSurat();
                    $this->status         = 'terbit';
                    $this->diterbitkan_at = now();
                    $this->save();

                    RegisterPelayanan::create([
                        'surat_id'          => $this->id,
                        'penduduk_id'       => $this->penduduk_id,
                        'petugas_id'        => $petugas->id,
                        'nomor_register'    => RegisterPelayanan::generateNomorRegister(),
                        'jenis_pelayanan'   => $this->jenisSurat->nama,
                        'pedukuhan_pemohon' => $this->penduduk->pedukuhan,
                        'tanggal_pelayanan' => today(),
                    ]);
                }, attempts: 5);

                return;
            } catch (QueryException $e) {
                $isDuplicate = ($e->errorInfo[1] ?? null) === 1062; // MySQL: duplicate entry
                if (! $isDuplicate || $attempt === $maxAttempts) {
                    throw $e;
                }
                // transaksi sudah di-rollback oleh DB::transaction() -> kembalikan state di memori
                // supaya percobaan berikutnya menghitung ulang nomor dari awal dengan benar.
                $this->status = 'draft';
            }
        }
    }

    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function ttd()
    {
        return $this->hasOne(TtdSurat::class);
    }

    public function register()
    {
        return $this->hasOne(RegisterPelayanan::class);
    }
}