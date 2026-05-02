<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * List semua user.
     */
    public function index(): JsonResponse
    {
        $users = User::orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'            => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'role'          => $u->role,
                'is_active'     => $u->is_active,
                'last_login_at' => $u->last_login_at?->format('d/m/Y H:i'),
            ]);

        return response()->json(['data' => $users]);
    }

    /**
     * POST /api/users
     * Tambah user baru.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,petugas',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => "User {$user->name} berhasil ditambahkan.",
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }

    /**
     * GET /api/users/{id}
     * Detail user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json([
            'data' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->role,
                'is_active'     => $user->is_active,
                'last_login_at' => $user->last_login_at?->format('d/m/Y H:i'),
                'created_at'    => $user->created_at->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * PUT /api/users/{id}
     * Edit user — nama, email, role.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:100',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => 'sometimes|in:admin,petugas',
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'message' => "User {$user->name} berhasil diupdate.",
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * PATCH /api/users/{id}/password
     * Ganti password user.
     */
    public function gantiPassword(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus semua token aktif user tersebut — paksa login ulang
        $user->tokens()->delete();

        return response()->json([
            'message' => "Password {$user->name} berhasil diganti. User harus login ulang.",
        ]);
    }

    /**
     * PATCH /api/users/{id}/toggle-aktif
     * Aktifkan / nonaktifkan user.
     */
    public function toggleAktif(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Tidak bisa nonaktifkan diri sendiri
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak bisa menonaktifkan akun sendiri.',
            ], 422);
        }

        // Pastikan minimal ada 1 admin aktif
        if ($user->isAdmin() && $user->is_active) {
            $adminAktifLain = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($adminAktifLain === 0) {
                return response()->json([
                    'message' => 'Tidak bisa menonaktifkan admin terakhir yang aktif.',
                ], 422);
            }
        }

        $user->update(['is_active' => ! $user->is_active]);

        // Jika dinonaktifkan, hapus semua token aktif
        if (! $user->is_active) {
            $user->tokens()->delete();
        }

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message'   => "User {$user->name} berhasil {$status}.",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Hapus user — hanya jika belum pernah membuat surat.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Tidak bisa hapus diri sendiri
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak bisa menghapus akun sendiri.',
            ], 422);
        }

        // Cek apakah user pernah membuat surat
        if ($user->suratDibuat()->exists()) {
            return response()->json([
                'message' => "User {$user->name} tidak bisa dihapus karena sudah memiliki riwayat pembuatan surat. Nonaktifkan saja.",
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => "User {$user->name} berhasil dihapus.",
        ]);
    }
}