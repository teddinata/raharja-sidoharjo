<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KelurahanSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/settings
     * Ambil konfigurasi kelurahan.
     */
    public function show(): JsonResponse
    {
        $setting = KelurahanSetting::instance();

        return response()->json([
            'data' => [
                'nama_kelurahan' => $setting->nama_kelurahan,
                'nama_kapanewon' => $setting->nama_kapanewon,
                'nama_kabupaten' => $setting->nama_kabupaten,
                'nama_provinsi'  => $setting->nama_provinsi,
                'nama_lengkap'   => $setting->nama_lengkap,
                'nama_lurah'     => $setting->nama_lurah,
                'nip_lurah'      => $setting->nip_lurah,
                'alamat'         => $setting->alamat,
                'kode_pos'       => $setting->kode_pos,
                'telepon'        => $setting->telepon,
                'email'          => $setting->email,
                'website'        => $setting->website,
                'logo_path'      => $setting->logo_path,
                'ttd_lurah_path' => $setting->ttd_lurah_path,
            ],
        ]);
    }

    /**
     * PUT /api/settings
     * Update konfigurasi kelurahan — hanya admin.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'nama_kelurahan' => 'sometimes|string|max:100',
            'nama_kapanewon' => 'sometimes|string|max:100',
            'nama_kabupaten' => 'sometimes|string|max:100',
            'nama_provinsi'  => 'sometimes|string|max:100',
            'nama_lurah'     => 'sometimes|nullable|string|max:100',
            'nip_lurah'      => 'sometimes|nullable|string|max:30',
            'alamat'         => 'sometimes|nullable|string',
            'kode_pos'       => 'sometimes|nullable|string|max:10',
            'telepon'        => 'sometimes|nullable|string|max:20',
            'email'          => 'sometimes|nullable|email|max:100',
            'website'        => 'sometimes|nullable|string|max:100',
        ]);

        $setting = KelurahanSetting::instance();
        $setting->fill($request->only([
            'nama_kelurahan', 'nama_kapanewon', 'nama_kabupaten', 'nama_provinsi',
            'nama_lurah', 'nip_lurah', 'alamat', 'kode_pos',
            'telepon', 'email', 'website',
        ]));
        $setting->updated_at = now();
        $setting->save();

        return response()->json([
            'message' => 'Pengaturan berhasil disimpan.',
            'data'    => $setting,
        ]);
    }
}