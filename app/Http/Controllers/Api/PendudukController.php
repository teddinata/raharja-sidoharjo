<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penduduk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PendudukController extends Controller
{
    /**
     * GET /api/penduduk?search=
     * Autocomplete by NIK atau nama, untuk dropdown di form surat.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        if (empty($search) || strlen($search) < 3) {
            return response()->json([
                'message' => 'Masukkan minimal 3 karakter untuk pencarian.',
                'data'    => [],
            ]);
        }

        $penduduk = Penduduk::aktif()
            ->search($search)
            ->select([
                'id', 'nik', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir',
                'jenis_kelamin', 'pedukuhan', 'rt', 'rw', 'pekerjaan',
                'status_perkawinan', 'hub_keluarga',
            ])
            ->limit(15)
            ->get()
            ->map(function ($p) {
                return [
                    'id'              => $p->id,
                    'nik'             => $p->nik,
                    'nama_lengkap'    => $p->nama_lengkap,
                    'tempat_lahir'    => $p->tempat_lahir,
                    'tanggal_lahir'   => $p->tanggal_lahir?->format('Y-m-d'),
                    'tanggal_lahir_format' => $p->tanggal_lahir_format,
                    'umur'            => $p->umur,
                    'jenis_kelamin'   => $p->jenis_kelamin,
                    'pedukuhan'       => $p->pedukuhan,
                    'rt'              => $p->rt,
                    'rw'              => $p->rw,
                    'pekerjaan'       => $p->pekerjaan,
                    'status_perkawinan' => $p->status_perkawinan,
                    'hub_keluarga'    => $p->hub_keluarga,
                ];
            });

        return response()->json([
            'data' => $penduduk,
        ]);
    }

    /**
     * GET /api/penduduk-list
     * List semua penduduk dengan paginasi untuk halaman manajemen data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = Penduduk::query();

        if ($request->has('search') && strlen($request->search) >= 2) {
            $query->search($request->search);
        }
        if ($request->has('pedukuhan') && $request->pedukuhan) {
            $query->where('pedukuhan', $request->pedukuhan);
        }
        if ($request->has('is_aktif') && $request->is_aktif !== null) {
            $query->where('is_aktif', filter_var($request->is_aktif, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min((int)($request->per_page ?? 15), 100);
        $paginator = $query->orderBy('nama_lengkap')->paginate($perPage);

        $data = collect($paginator->items())->map(fn($p) => $this->formatDetail($p));

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/penduduk/{nik}
     * Detail lengkap warga by NIK — untuk auto-fill form surat.
     */
    public function show(string $nik): JsonResponse
    {
        $penduduk = Penduduk::where('nik', $nik)->first();

        if (! $penduduk) {
            return response()->json([
                'message' => "Penduduk dengan NIK {$nik} tidak ditemukan.",
            ], 404);
        }

        return response()->json(['data' => $this->formatDetail($penduduk)]);
    }

    /**
     * POST /api/penduduk
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $penduduk = Penduduk::create(array_merge(
            $validator->validated(),
            ['is_aktif' => true]
        ));

        return response()->json([
            'message' => 'Data penduduk berhasil ditambahkan.',
            'data'    => $this->formatDetail($penduduk),
        ], 201);
    }

    /**
     * PUT /api/penduduk/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $penduduk = Penduduk::findOrFail($id);

        $validator = Validator::make($request->all(), $this->rules($id));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $penduduk->update($validator->validated());

        return response()->json([
            'message' => 'Data penduduk berhasil diperbarui.',
            'data'    => $this->formatDetail($penduduk->fresh()),
        ]);
    }

    /**
     * PATCH /api/penduduk/{id}/toggle-aktif
     */
    public function toggleAktif(int $id): JsonResponse
    {
        $penduduk = Penduduk::findOrFail($id);
        $penduduk->update(['is_aktif' => ! $penduduk->is_aktif]);

        return response()->json([
            'message'  => $penduduk->is_aktif ? 'Penduduk diaktifkan.' : 'Penduduk dinonaktifkan.',
            'is_aktif' => $penduduk->is_aktif,
        ]);
    }

    /**
     * DELETE /api/penduduk/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $penduduk = Penduduk::findOrFail($id);

        if ($penduduk->surat()->exists()) {
            return response()->json([
                'message' => 'Penduduk tidak dapat dihapus karena memiliki riwayat surat.',
            ], 409);
        }

        $penduduk->delete();

        return response()->json(['message' => 'Data penduduk berhasil dihapus.']);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function formatDetail(Penduduk $p): array
    {
        return [
            'id'                   => $p->id,
            'no_kk'                => $p->no_kk,
            'nik'                  => $p->nik,
            'nama_lengkap'         => $p->nama_lengkap,
            'tempat_lahir'         => $p->tempat_lahir,
            'tanggal_lahir'        => $p->tanggal_lahir?->format('Y-m-d'),
            'tanggal_lahir_format' => $p->tanggal_lahir_format,
            'umur'                 => $p->umur,
            'jenis_kelamin'        => $p->jenis_kelamin,
            'bin_binti'            => $p->bin_binti,
            'agama'                => $p->agama,
            'pendidikan'           => $p->pendidikan,
            'pekerjaan'            => $p->pekerjaan,
            'status_perkawinan'    => $p->status_perkawinan,
            'hub_keluarga'         => $p->hub_keluarga,
            'pedukuhan'            => $p->pedukuhan,
            'rt'                   => $p->rt,
            'rw'                   => $p->rw,
            'alamat_lengkap'       => $p->alamat_lengkap,
            'nama_ketua_rt'        => $p->nama_ketua_rt,
            'nama_ketua_rw'        => $p->nama_ketua_rw,
            'nama_ayah'            => $p->nama_ayah,
            'nama_ibu'             => $p->nama_ibu,
            'is_aktif'             => $p->is_aktif,
        ];
    }

    private function rules(?int $ignoreId = null): array
    {
        $nikUnique = 'unique:penduduk,nik' . ($ignoreId ? ",{$ignoreId}" : '');
        return [
            'no_kk'            => 'nullable|string|max:20',
            'nik'              => "required|string|size:16|{$nikUnique}",
            'nama_lengkap'     => 'required|string|max:100',
            'tempat_lahir'     => 'required|string|max:100',
            'tanggal_lahir'    => 'required|date',
            'jenis_kelamin'    => 'required|in:Laki-laki,Perempuan',
            'agama'            => 'required|string|max:20',
            'pendidikan'       => 'nullable|string|max:50',
            'pekerjaan'        => 'required|string|max:100',
            'status_perkawinan'=> 'required|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',
            'hub_keluarga'     => 'required|string|max:50',
            'pedukuhan'        => 'required|string|max:50',
            'rt'               => 'required|string|max:5',
            'rw'               => 'required|string|max:5',
            'nama_ketua_rt'    => 'nullable|string|max:100',
            'nama_ketua_rw'    => 'nullable|string|max:100',
            'nama_ayah'        => 'nullable|string|max:100',
            'nama_ibu'         => 'nullable|string|max:100',
        ];
    }
}