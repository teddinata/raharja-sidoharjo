<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use Illuminate\Http\JsonResponse;

class JenisSuratController extends Controller
{
    /**
     * GET /api/jenis-surat
     * List semua jenis surat aktif, dikelompokkan per kategori.
     */
    public function index(): JsonResponse
    {
        $data = JenisSurat::aktif()
            ->select(['id', 'kode', 'nama', 'kategori', 'nomor_format', 'melibatkan_pihak_luar', 'urutan'])
            ->get()
            ->groupBy('kategori')
            ->map(fn($items) => $items->values());

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * GET /api/jenis-surat/{kode}
     * Detail jenis surat + fields_tambahan untuk render form dinamis di frontend.
     */
    public function show(string $kode): JsonResponse
    {
        $jenis = JenisSurat::where('kode', $kode)->where('is_active', true)->first();

        if (! $jenis) {
            return response()->json([
                'message' => "Jenis surat dengan kode {$kode} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'data' => [
                'id'                    => $jenis->id,
                'kode'                  => $jenis->kode,
                'nama'                  => $jenis->nama,
                'kategori'              => $jenis->kategori,
                'nomor_format'          => $jenis->nomor_format,
                'fields_tambahan'       => $jenis->fields_tambahan ?? [],
                'melibatkan_pihak_luar' => $jenis->melibatkan_pihak_luar,
                'template_blade'        => $jenis->template_blade,
            ],
        ]);
    }
}