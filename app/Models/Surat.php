<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'penduduk_id', 'jenis_surat_id', 'dibuat_oleh',
    'nomor_surat', 'data_tambahan', 'data_pihak_luar',
    'status', 'file_pdf_path', 'file_docx_path', 'dicetak_at',
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
        ];
    }

    public function terbitkan(User $petugas): void
    {
        if ($this->status !== 'draft') {
            throw new \LogicException("Surat berstatus [{$this->status}] tidak bisa diterbitkan.");
        }

        $this->nomor_surat = $this->jenisSurat->generateNomorSurat();
        $this->status      = 'terbit';
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