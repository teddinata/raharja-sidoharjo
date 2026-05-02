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
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penduduk_id')->constrained('penduduk')->restrictOnDelete();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surat')->restrictOnDelete();
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->string('nomor_surat')->nullable()->index();
            $table->json('data_tambahan')->nullable();    // isian field ekstra
            $table->json('data_pihak_luar')->nullable(); // data pihak dari luar desa
            $table->enum('status', ['draft', 'terbit', 'batal'])->default('draft');
            $table->string('file_pdf_path')->nullable();
            $table->string('file_docx_path')->nullable();
            $table->timestamp('dicetak_at')->nullable();
            $table->timestamps();

            $table->index(['jenis_surat_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
