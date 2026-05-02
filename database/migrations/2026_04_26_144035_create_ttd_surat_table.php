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
        Schema::create('ttd_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->unique()->constrained('surat')->cascadeOnDelete();
            $table->string('atas_nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('nip')->nullable();
            $table->string('ttd_image_path')->nullable();
            $table->timestamp('ditandatangani_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttd_surat');
    }
};
