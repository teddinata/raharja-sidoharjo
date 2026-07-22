<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\Surat;
use App\Models\TtdSurat;
use App\Models\KelurahanSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

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
     * Override relasi ttd surat sesuai penandatangan yang dipilih saat cetak (lurah/carik).
     * Default (tidak dipilih / nilai lain) tetap pakai ttd tersimpan di surat (Lurah).
     */
    private function resolveTtd(Surat $surat, KelurahanSetting $setting, ?string $penandatangan): void
    {
        if ($penandatangan === 'carik') {
            $surat->setRelation('ttd', new TtdSurat([
                'surat_id'       => $surat->id,
                'atas_nama'      => $setting->nama_carik,
                'jabatan'        => "Carik {$setting->nama_kelurahan}",
                'nip'            => $setting->nip_carik,
                'ttd_image_path' => $setting->ttd_carik_path,
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

        $this->resolveTtd($surat, $setting, $request->query('penandatangan'));

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

        $this->resolveTtd($surat, $setting, $request->query('penandatangan'));

        $phpWord  = new PhpWord();
        $section  = $phpWord->addSection();
        $penduduk = $surat->penduduk;
        $ttd      = $surat->ttd;
        $extra    = $surat->data_tambahan ?? [];

        // ── KOP SURAT ────────────────────────────────────────
        $section->addText(
            strtoupper("KALURAHAN {$setting->nama_kelurahan}"),
            ['bold' => true, 'size' => 14],
            ['alignment' => 'center']
        );
        $section->addText(
            "Kapanewon {$setting->nama_kapanewon}, Kabupaten {$setting->nama_kabupaten}",
            ['size' => 11],
            ['alignment' => 'center']
        );
        if ($setting->alamat) {
            $section->addText($setting->alamat, ['size' => 10], ['alignment' => 'center']);
        }
        $section->addLine(['weight' => 2, 'color' => '000000']);
        $section->addTextBreak(1);

        // ── JUDUL ─────────────────────────────────────────────
        $section->addText(
            strtoupper($surat->jenisSurat->nama),
            ['bold' => true, 'size' => 12, 'underline' => 'single'],
            ['alignment' => 'center']
        );
        $section->addText(
            "Nomor: {$surat->nomor_surat}",
            ['size' => 11],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        // ── ISI SURAT ─────────────────────────────────────────
        $section->addText(
            "Yang bertanda tangan di bawah ini, Lurah {$setting->nama_kelurahan}, Kapanewon {$setting->nama_kapanewon}, Kabupaten {$setting->nama_kabupaten}, menerangkan bahwa:",
            ['size' => 11],
            ['spaceAfter' => 120]
        );

        // Tabel data pemohon
        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $fields = [
            ['Nama',              $penduduk->nama_lengkap],
            ['NIK',               $penduduk->nik ?? '-'],
            ['Tempat/Tgl Lahir',  ($penduduk->tempat_lahir ?? '-') . ', ' . ($penduduk->tanggal_lahir_format ?? '-')],
            ['Jenis Kelamin',     $penduduk->jenis_kelamin ?? '-'],
            ['Agama',             $penduduk->agama ?? '-'],
            ['Pekerjaan',         $penduduk->pekerjaan ?? '-'],
            ['Alamat',            $penduduk->alamat_lengkap . ', ' . $setting->nama_kelurahan . ', ' . $setting->nama_kapanewon . ', ' . $setting->nama_kabupaten],
        ];

        foreach ($fields as [$label, $value]) {
            $row = $table->addRow();
            $row->addCell(2500)->addText($label, ['size' => 11]);
            $row->addCell(300)->addText(':', ['size' => 11]);
            $row->addCell(6000)->addText($value, ['size' => 11]);
        }

        // Field tambahan spesifik per jenis surat
        if (! empty($extra)) {
            foreach ($extra as $key => $value) {
                if (! empty($value)) {
                    $row = $table->addRow();
                    $row->addCell(2500)->addText(ucwords(str_replace('_', ' ', $key)), ['size' => 11]);
                    $row->addCell(300)->addText(':', ['size' => 11]);
                    $row->addCell(6000)->addText((string) $value, ['size' => 11]);
                }
            }
        }

        $section->addTextBreak(1);
        $section->addText(
            "Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.",
            ['size' => 11]
        );
        $section->addTextBreak(2);

        // ── TTD ───────────────────────────────────────────────
        $now = now()->translatedFormat('d F Y');
        $section->addText(
            "{$setting->nama_kelurahan}, {$now}",
            ['size' => 11],
            ['alignment' => 'right']
        );
        $section->addText(
            ($ttd ? $ttd->jabatan : null) ?? "Lurah {$setting->nama_kelurahan}",
            ['size' => 11],
            ['alignment' => 'right']
        );
        $section->addTextBreak(3);
        $section->addText(
            ($ttd ? $ttd->atas_nama : $setting->nama_lurah) ?? '.....................',
            ['bold' => true, 'underline' => 'single', 'size' => 11],
            ['alignment' => 'right']
        );
        if ($ttd?->nip) {
            $section->addText(
                "NIP. {$ttd->nip}",
                ['size' => 11],
                ['alignment' => 'right']
            );
        }

        // ── SIMPAN & DOWNLOAD ─────────────────────────────────
        $filename = str_replace(['/', '\\'], '-', $surat->nomor_surat) . '.docx';
        $tmpDir   = storage_path('app/tmp');
        $tmpPath  = "{$tmpDir}/{$filename}";

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpPath);

        $surat->update(['dicetak_at' => now()]);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}