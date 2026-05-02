<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KelurahanSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * POST /api/upload/logo
     * Upload logo kalurahan untuk kop surat.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $setting = KelurahanSetting::instance();

        // Hapus logo lama jika ada
        if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
            Storage::disk('public')->delete($setting->logo_path);
        }

        $path = $request->file('logo')->storeAs(
            'kelurahan',
            'logo.' . $request->file('logo')->extension(),
            'public'
        );

        $setting->logo_path = $path;
        $setting->updated_at = now();
        $setting->save();

        return response()->json([
            'message'   => 'Logo berhasil diupload.',
            'logo_path' => $path,
            'logo_url'  => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * POST /api/upload/ttd-lurah
     * Upload tanda tangan digital Lurah.
     */
    public function uploadTtdLurah(Request $request): JsonResponse
    {
        $request->validate([
            'ttd' => 'required|image|mimes:png|max:1024',
        ]);

        $setting = KelurahanSetting::instance();

        // Hapus TTD lama jika ada
        if ($setting->ttd_lurah_path && Storage::disk('public')->exists($setting->ttd_lurah_path)) {
            Storage::disk('public')->delete($setting->ttd_lurah_path);
        }

        $path = $request->file('ttd')->storeAs(
            'kelurahan',
            'ttd_lurah.png',
            'public'
        );

        $setting->ttd_lurah_path = $path;
        $setting->updated_at = now();
        $setting->save();

        // Update semua TtdSurat yang belum ada ttd_image_path-nya
        \App\Models\TtdSurat::whereNull('ttd_image_path')->update([
            'ttd_image_path' => $path,
        ]);

        return response()->json([
            'message'       => 'Tanda tangan Lurah berhasil diupload.',
            'ttd_path'      => $path,
            'ttd_url'       => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * DELETE /api/upload/ttd-lurah
     * Hapus tanda tangan digital Lurah.
     */
    public function deleteTtdLurah(): JsonResponse
    {
        $setting = KelurahanSetting::instance();

        if ($setting->ttd_lurah_path && Storage::disk('public')->exists($setting->ttd_lurah_path)) {
            Storage::disk('public')->delete($setting->ttd_lurah_path);
        }

        $setting->ttd_lurah_path = null;
        $setting->updated_at = now();
        $setting->save();

        return response()->json([
            'message' => 'Tanda tangan Lurah berhasil dihapus.',
        ]);
    }
}