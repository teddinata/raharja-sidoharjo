<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RukunTetangga;
use App\Models\RukunWarga;
use App\Support\WilayahRtRw;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master Ketua RT & Ketua RW. Mengganti nama ketua di sini langsung berlaku
 * untuk semua warga di RT/RW tersebut, termasuk surat yang dicetak berikutnya.
 */
class RtRwController extends Controller
{
    /**
     * GET /api/rt-rw
     * Semua RW beserta RT-nya, diurutkan menurut nomor, lengkap dengan jumlah warga aktif.
     */
    public function index(): JsonResponse
    {
        $jumlahWarga = [];

        foreach (DB::table('penduduk')->where('is_aktif', true)
            ->selectRaw('rw, rt, count(*) as n')->groupBy('rw', 'rt')->get() as $g) {
            $rw = WilayahRtRw::nomor($g->rw);
            $rt = WilayahRtRw::nomor($g->rt);

            if ($rw !== null && $rt !== null) {
                $jumlahWarga["{$rw}/{$rt}"] = ($jumlahWarga["{$rw}/{$rt}"] ?? 0) + $g->n;
            }
        }

        $data = RukunWarga::with('rukunTetangga')->get()
            ->sortBy(fn (RukunWarga $rw) => (int) $rw->nomor)
            ->values()
            ->map(function (RukunWarga $rw) use ($jumlahWarga) {
                $rt = $rw->rukunTetangga
                    ->sortBy(fn (RukunTetangga $rt) => (int) $rt->nomor)
                    ->values()
                    ->map(fn (RukunTetangga $rt) => [
                        'id'            => $rt->id,
                        'nomor'         => $rt->nomor,
                        'nama_ketua'    => $rt->nama_ketua,
                        'jumlah_warga'  => $jumlahWarga["{$rw->nomor}/{$rt->nomor}"] ?? 0,
                    ]);

                return [
                    'id'           => $rw->id,
                    'nomor'        => $rw->nomor,
                    'pedukuhan'    => $rw->pedukuhan,
                    'nama_ketua'   => $rw->nama_ketua,
                    'jumlah_warga' => $rt->sum('jumlah_warga'),
                    'rt'           => $rt,
                ];
            });

        return response()->json(['data' => $data]);
    }

    /**
     * PUT /api/rw/{id}
     */
    public function updateRw(Request $request, int $id): JsonResponse
    {
        $rw = RukunWarga::findOrFail($id);

        $rw->update($request->validate([
            'nama_ketua' => 'nullable|string|max:100',
            'pedukuhan'  => 'nullable|string|max:50',
        ]));

        return response()->json([
            'message' => "Ketua RW {$rw->nomor} berhasil diperbarui.",
            'data'    => $rw->only(['id', 'nomor', 'pedukuhan', 'nama_ketua']),
        ]);
    }

    /**
     * PUT /api/rt/{id}
     */
    public function updateRt(Request $request, int $id): JsonResponse
    {
        $rt = RukunTetangga::with('rukunWarga')->findOrFail($id);

        $rt->update($request->validate([
            'nama_ketua' => 'nullable|string|max:100',
        ]));

        return response()->json([
            'message' => "Ketua RT {$rt->nomor} / RW {$rt->rukunWarga->nomor} berhasil diperbarui.",
            'data'    => $rt->only(['id', 'nomor', 'nama_ketua']),
        ]);
    }
}
