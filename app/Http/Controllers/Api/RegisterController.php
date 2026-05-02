<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegisterPelayanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RegisterExport;

class RegisterController extends Controller
{
    /**
     * GET /api/register
     * Riwayat pelayanan dengan filter.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'dari'       => 'nullable|date',
            'sampai'     => 'nullable|date|after_or_equal:dari',
            'pedukuhan'  => 'nullable|string',
            'jenis'      => 'nullable|string',
            'per_page'   => 'nullable|integer|min:5|max:100',
        ]);

        $query = RegisterPelayanan::with(['penduduk:id,nik,nama_lengkap', 'petugas:id,name', 'surat.jenisSurat:id,nama,kode'])
            ->orderByDesc('tanggal_pelayanan')
            ->orderByDesc('id');

        // Filter tanggal
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_pelayanan', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_pelayanan', '<=', $request->sampai);
        }

        // Filter pedukuhan
        if ($request->filled('pedukuhan')) {
            $query->where('pedukuhan_pemohon', $request->pedukuhan);
        }

        // Filter jenis pelayanan
        if ($request->filled('jenis')) {
            $query->where('jenis_pelayanan', 'like', "%{$request->jenis}%");
        }

        $perPage = $request->integer('per_page', 20);
        $data    = $query->paginate($perPage);

        return response()->json([
            'data' => $data->map(function ($r) {
                return [
                    'id'               => $r->id,
                    'nomor_register'   => $r->nomor_register,
                    'tanggal'          => $r->tanggal_pelayanan->format('d/m/Y'),
                    'jenis_pelayanan'  => $r->jenis_pelayanan,
                    'nomor_surat'      => $r->surat?->nomor_surat,
                    'nama_pemohon'     => $r->penduduk?->nama_lengkap,
                    'nik_pemohon'      => $r->penduduk?->nik,
                    'pedukuhan'        => $r->pedukuhan_pemohon,
                    'petugas'          => $r->petugas?->name,
                    'keterangan'       => $r->keterangan,
                ];
            }),
            'meta' => [
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/register/rekap
     * Rekap jumlah surat per jenis dan per pedukuhan.
     */
    public function rekap(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'nullable|integer|min:2020|max:2099',
        ]);

        $tahun = $request->integer('tahun', now()->year);

        // Rekap per jenis pelayanan
        $perJenis = RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun)
            ->selectRaw('jenis_pelayanan, COUNT(*) as jumlah')
            ->groupBy('jenis_pelayanan')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn($r) => [
                'jenis'  => $r->jenis_pelayanan,
                'jumlah' => $r->jumlah,
            ]);

        // Rekap per pedukuhan
        $perPedukuhan = RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun)
            ->whereNotNull('pedukuhan_pemohon')
            ->selectRaw('pedukuhan_pemohon, COUNT(*) as jumlah')
            ->groupBy('pedukuhan_pemohon')
            ->orderBy('pedukuhan_pemohon')
            ->get()
            ->map(fn($r) => [
                'pedukuhan' => $r->pedukuhan_pemohon,
                'jumlah'    => $r->jumlah,
            ]);

        // Rekap per bulan
        $perBulan = RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun)
            ->selectRaw('MONTH(tanggal_pelayanan) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(fn($r) => [
                'bulan'  => \Carbon\Carbon::create()->month($r->bulan)->translatedFormat('F'),
                'jumlah' => $r->jumlah,
            ]);

        return response()->json([
            'tahun'         => $tahun,
            'total'         => RegisterPelayanan::whereYear('tanggal_pelayanan', $tahun)->count(),
            'per_jenis'     => $perJenis,
            'per_pedukuhan' => $perPedukuhan,
            'per_bulan'     => $perBulan,
        ]);
    }

    /**
     * GET /api/register/export
     * Export register ke Excel.
     */
    public function export(Request $request)
    {
        $request->validate([
            'dari'      => 'nullable|date',
            'sampai'    => 'nullable|date',
            'pedukuhan' => 'nullable|string',
            'tahun'     => 'nullable|integer',
        ]);

        $filters = $request->only(['dari', 'sampai', 'pedukuhan', 'tahun']);
        $tahun   = $filters['tahun'] ?? now()->year;
        $filename = "Register_Pelayanan_{$tahun}.xlsx";

        return Excel::download(new RegisterExport($filters), $filename);
    }
}