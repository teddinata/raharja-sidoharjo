<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\Surat;
use App\Models\TtdSurat;
use App\Models\KelurahanSetting;
use App\Support\SuratDocxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    /**
     * POST /api/surat
     * Buat surat baru (status draft dulu).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'penduduk_id'      => 'required|exists:penduduk,id',
            'jenis_surat_kode' => 'required|exists:jenis_surat,kode',
            'data_tambahan'    => 'nullable|array',
            'data_pihak_luar'  => 'nullable|array',
            'langsung_terbit'  => 'boolean', // true = langsung terbit, false = draft
        ]);

        $jenisSurat = JenisSurat::where('kode', $request->jenis_surat_kode)->firstOrFail();
        $penduduk   = Penduduk::findOrFail($request->penduduk_id);

        $surat = Surat::create([
            'penduduk_id'    => $penduduk->id,
            'jenis_surat_id' => $jenisSurat->id,
            'dibuat_oleh'    => $request->user()->id,
            'data_tambahan'  => $request->data_tambahan ?? [],
            'data_pihak_luar'=> $request->data_pihak_luar ?? [],
            'status'         => 'draft',
        ]);

        // Auto-generate TTD dari setting lurah
        TtdSurat::dariSettingLurah($surat->id);

        // Langsung terbitkan jika diminta
        if ($request->boolean('langsung_terbit', true)) {
            $surat->terbitkan($request->user());
        }

        $surat->load(['penduduk', 'jenisSurat', 'ttd', 'register']);

        return response()->json([
            'message' => $surat->status === 'terbit'
                ? "Surat {$jenisSurat->nama} berhasil diterbitkan."
                : "Surat {$jenisSurat->nama} berhasil dibuat sebagai draft.",
            'data' => [
                'id'           => $surat->id,
                'nomor_surat'  => $surat->nomor_surat,
                'status'       => $surat->status,
                'jenis_surat'  => $jenisSurat->nama,
                'penduduk'     => $penduduk->nama_lengkap,
                'dibuat_pada'  => $surat->created_at->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /**
     * GET /api/surat/{id}
     * Detail surat.
     */
    public function show(int $id): JsonResponse
    {
        $surat = Surat::with(['penduduk', 'jenisSurat', 'ttd', 'register', 'dibuatOleh'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id'              => $surat->id,
                'nomor_surat'     => $surat->nomor_surat,
                'status'          => $surat->status,
                'jenis_surat'     => $surat->jenisSurat->nama,
                'kategori'        => $surat->jenisSurat->kategori,
                'penduduk'        => [
                    'nik'          => $surat->penduduk->nik,
                    'nama_lengkap' => $surat->penduduk->nama_lengkap,
                    'pedukuhan'    => $surat->penduduk->pedukuhan,
                ],
                'data_tambahan'   => $surat->data_tambahan,
                'data_pihak_luar' => $surat->data_pihak_luar,
                'ttd'             => $surat->ttd ? [
                    'atas_nama' => $surat->ttd->atas_nama,
                    'jabatan'   => $surat->ttd->jabatan,
                    'nip'       => $surat->ttd->nip,
                ] : null,
                'dibuat_oleh'     => $surat->dibuatOleh->name,
                'dibuat_pada'     => $surat->created_at->format('d/m/Y H:i'),
                'dicetak_at'      => $surat->dicetak_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * PUT /api/surat/{id}
     * Koreksi isi surat (data_tambahan / data_pihak_luar).
     * Nomor surat & penduduk tidak bisa diubah.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $surat = Surat::with(['jenisSurat'])->findOrFail($id);

        $request->validate([
            'data_tambahan'   => 'nullable|array',
            'data_pihak_luar' => 'nullable|array',
        ]);

        $surat->update($request->only(['data_tambahan', 'data_pihak_luar']));

        return response()->json([
            'message'       => 'Data surat berhasil diperbarui.',
            'data_tambahan' => $surat->fresh()->data_tambahan,
        ]);
    }

    /**
     * Override relasi ttd surat sesuai penandatangan yang dipilih saat cetak (lurah/carik/an_lurah).
     * Default (tidak dipilih / nilai lain) tetap pakai ttd tersimpan di surat (Lurah).
     */
    private function resolveTtd(Surat $surat, KelurahanSetting $setting, Request $request): void
    {
        $penandatangan = $request->query('penandatangan');

        if ($penandatangan === 'carik') {
            $surat->setRelation('ttd', new TtdSurat([
                'surat_id'       => $surat->id,
                'atas_nama'      => $setting->nama_carik,
                'jabatan'        => "An Lurah {$setting->nama_kelurahan}\nCarik",
                'nip'            => $setting->nip_carik,
                'ttd_image_path' => $setting->ttd_carik_path,
            ]));
        } elseif ($penandatangan === 'an_lurah') {
            // Penandatangan "An Lurah" tanpa jabatan tetap di sistem (mis. Pj/Plt situasional) —
            // namanya diisi manual saat cetak, dikosongkan (garis titik-titik) kalau tidak diisi.
            $namaManual = trim((string) $request->query('nama_manual', ''));
            $surat->setRelation('ttd', new TtdSurat([
                'surat_id'       => $surat->id,
                'atas_nama'      => $namaManual !== '' ? $namaManual : null,
                'jabatan'        => "An Lurah {$setting->nama_kelurahan}",
                'nip'            => null,
                'ttd_image_path' => null,
            ]));
        }
    }

    /**
     * GET /api/surat/{id}/pdf
     * Generate dan download PDF surat.
     */
    public function downloadPdf(Request $request, int $id)
    {
        $surat    = Surat::with(['penduduk', 'jenisSurat', 'ttd'])->findOrFail($id);
        $setting  = KelurahanSetting::instance();

        if ($surat->status !== 'terbit') {
            return response()->json([
                'message' => 'Hanya surat berstatus terbit yang bisa didownload.',
            ], 422);
        }

        $this->resolveTtd($surat, $setting, $request);

        $template = $surat->jenisSurat->template_blade;

        if (! $template || ! view()->exists($template)) {
            return response()->json([
                'message' => "Template [{$template}] belum tersedia.",
            ], 404);
        }

        $pdf = Pdf::loadView($template, [
            'surat'   => $surat,
            'setting' => $setting,
        ])->setPaper('a4', 'portrait');

        // Catat waktu cetak
        $surat->update(['dicetak_at' => now()]);

        $filename = str_replace('/', '-', $surat->nomor_surat) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * GET /api/surat/{id}/docx
     * Generate dan download DOCX surat.
     */
    public function downloadDocx(Request $request, int $id)
    {
        $surat   = Surat::with(['penduduk', 'jenisSurat', 'ttd'])->findOrFail($id);
        $setting = KelurahanSetting::instance();

        if ($surat->status !== 'terbit') {
            return response()->json([
                'message' => 'Hanya surat berstatus terbit yang bisa didownload.',
            ], 422);
        }

        $this->resolveTtd($surat, $setting, $request);

        $template = $surat->jenisSurat->template_blade;

        if (! $template || ! view()->exists($template)) {
            return response()->json([
                'message' => "Template [{$template}] belum tersedia.",
            ], 404);
        }

        // Render blade yang sama persis dengan PDF, supaya isi & susunan surat di DOCX
        // dijamin sama dengan PDF untuk semua jenis surat (tidak ada struktur generik terpisah).
        $html = view($template, [
            'surat'   => $surat,
            'setting' => $setting,
        ])->render();

        $filename = str_replace(['/', '\\'], '-', $surat->nomor_surat) . '.docx';
        $tmpDir   = storage_path('app/tmp');
        $tmpPath  = "{$tmpDir}/{$filename}";

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        SuratDocxWriter::save($html, $tmpPath);

        $surat->update(['dicetak_at' => now()]);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}