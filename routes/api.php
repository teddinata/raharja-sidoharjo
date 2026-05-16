<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JenisSuratController;
use App\Http\Controllers\Api\PendudukController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SuratController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// ── Protected ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Master data
    Route::get('/penduduk',               [PendudukController::class, 'index']);
    Route::get('/penduduk/{nik}',          [PendudukController::class, 'show']);
    Route::post('/penduduk',              [PendudukController::class, 'store']);
    Route::put('/penduduk/{id}',          [PendudukController::class, 'update']);
    Route::patch('/penduduk/{id}/toggle-aktif', [PendudukController::class, 'toggleAktif']);
    Route::delete('/penduduk/{id}',       [PendudukController::class, 'destroy']);
    Route::get('/penduduk-list',          [PendudukController::class, 'list']);
    Route::get('/jenis-surat',       [JenisSuratController::class, 'index']);
    Route::get('/jenis-surat/{kode}',[JenisSuratController::class, 'show']);
    Route::get('/settings',          [SettingController::class, 'show']);


    // Di dalam group auth:sanctum, tambahkan:
    Route::post('/surat',               [SuratController::class, 'store']);
    Route::get('/surat/{id}',           [SuratController::class, 'show']);
    Route::put('/surat/{id}',           [SuratController::class, 'update']);
    Route::get('/surat/{id}/pdf',       [SuratController::class, 'downloadPdf']);
    Route::get('/surat/{id}/docx',      [SuratController::class, 'downloadDocx']);

    Route::get('/register',             [RegisterController::class, 'index']);
    Route::get('/register/rekap',       [RegisterController::class, 'rekap']);
    Route::get('/register/export',      [RegisterController::class, 'export']);

    // ── Admin only ────────────────────────────────────────────
    Route::middleware('auth.admin')->group(function () {
        Route::put('/register/{id}',    [RegisterController::class, 'update']);
        Route::delete('/register/{id}', [RegisterController::class, 'destroy']);
    });

    // ── Admin only ────────────────────────────────────────────
    Route::middleware('auth.admin')->group(function () {
        Route::put('/settings', [SettingController::class, 'update']);
        Route::post('/upload/logo',          [UploadController::class, 'uploadLogo']);
        Route::post('/upload/ttd-lurah',     [UploadController::class, 'uploadTtdLurah']);
        Route::delete('/upload/ttd-lurah',   [UploadController::class, 'deleteTtdLurah']);
    
        // User management
        Route::get('/users',                          [UserController::class, 'index']);
        Route::post('/users',                         [UserController::class, 'store']);
        Route::get('/users/{id}',                     [UserController::class, 'show']);
        Route::put('/users/{id}',                     [UserController::class, 'update']);
        Route::patch('/users/{id}/password',          [UserController::class, 'gantiPassword']);
        Route::patch('/users/{id}/toggle-aktif',      [UserController::class, 'toggleAktif']);
        Route::delete('/users/{id}',                  [UserController::class, 'destroy']);
    });

});