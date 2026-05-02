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
        Schema::create('jenis_surat', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // e.g. DOMISILI, SKM, NIKAH_P
            $table->string('nama');
            $table->string('kategori');
            $table->string('nomor_format')->nullable(); // e.g. 474.2, 474.4
            $table->json('fields_tambahan')->nullable(); // definisi field ekstra, dibaca frontend
            $table->boolean('melibatkan_pihak_luar')->default(false);
            $table->string('template_blade')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_surat');
    }
};
