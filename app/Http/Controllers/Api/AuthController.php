<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Maksimal sesi login aktif bersamaan per user. */
    private const MAX_SESSIONS = 2;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Catat waktu login terakhir
        $user->update(['last_login_at' => now()]);

        $deviceLabel = UserAgentParser::label($request->userAgent());

        $newToken = $user->createToken($deviceLabel);
        $newToken->accessToken->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ])->save();

        // Batasi maksimal sesi aktif bersamaan — hapus sesi terlama kalau melebihi batas.
        // (pakai slice() di PHP, bukan skip()/offset() di query — MySQL tidak izinkan OFFSET tanpa LIMIT)
        $excessTokenIds = $user->tokens()
            ->orderByDesc('created_at')
            ->pluck('id')
            ->slice(self::MAX_SESSIONS);
        if ($excessTokenIds->isNotEmpty()) {
            $user->tokens()->whereIn('id', $excessTokenIds)->delete();
        }

        $token = $newToken->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => [
                'id'            => $request->user()->id,
                'name'          => $request->user()->name,
                'email'         => $request->user()->email,
                'role'          => $request->user()->role,
                'last_login_at' => $request->user()->last_login_at,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * GET /api/auth/sessions
     * Daftar sesi login aktif milik user yang sedang login.
     */
    public function sessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()->id;

        $sessions = $request->user()->tokens()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($token) => [
                'id'            => $token->id,
                'device'        => $token->name,
                'ip_address'    => $token->ip_address,
                'last_used_at'  => $token->last_used_at,
                'created_at'    => $token->created_at,
                'is_current'    => $token->id === $currentTokenId,
            ]);

        return response()->json(['data' => $sessions]);
    }

    /**
     * DELETE /api/auth/sessions/{id}
     * Cabut satu sesi login tertentu (misal karena mencurigakan).
     */
    public function revokeSession(Request $request, int $id): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $id)->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Sesi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Sesi berhasil dicabut.',
        ]);
    }
}