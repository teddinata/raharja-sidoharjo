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
        Schema::table('kelurahan_settings', function (Blueprint $table) {
            $table->string('nama_carik')->nullable()->after('nip_lurah');
            $table->string('nip_carik')->nullable()->after('nama_carik');
            $table->string('ttd_carik_path')->nullable()->after('ttd_lurah_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelurahan_settings', function (Blueprint $table) {
            $table->dropColumn(['nama_carik', 'nip_carik', 'ttd_carik_path']);
        });
    }
};
