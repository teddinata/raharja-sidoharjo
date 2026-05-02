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
        Schema::create('kelurahan_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelurahan')->default('Sidoharjo');
            $table->string('nama_kapanewon')->default('Samigaluh');
            $table->string('nama_kabupaten')->default('Kulon Progo');
            $table->string('nama_provinsi')->default('Daerah Istimewa Yogyakarta');
            $table->string('nama_lurah')->nullable();
            $table->string('nip_lurah')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('ttd_lurah_path')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahan_settings');
    }
};
