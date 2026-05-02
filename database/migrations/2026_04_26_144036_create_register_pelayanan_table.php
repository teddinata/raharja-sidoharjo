<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('register_pelayanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->unique()->constrained('surat')->cascadeOnDelete();
            $table->foreignId('penduduk_id')->constrained('penduduk')->restrictOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->string('nomor_register')->unique(); // e.g. REG-2026-001
            $table->string('jenis_pelayanan'); // nama surat saat terbit, disimpan as-is untuk histori
            $table->string('pedukuhan_pemohon')->nullable();
            $table->date('tanggal_pelayanan');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal_pelayanan');
            $table->index('pedukuhan_pemohon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_pelayanan');
    }
};
