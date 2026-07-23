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
        Schema::table('surat', function (Blueprint $table) {
            $table->timestamp('diterbitkan_at')->nullable()->after('status');
        });

        // Backfill surat yang sudah terbit sebelum kolom ini ada, pakai created_at sebagai estimasi.
        \DB::table('surat')
            ->where('status', 'terbit')
            ->whereNull('diterbitkan_at')
            ->update(['diterbitkan_at' => \DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropColumn('diterbitkan_at');
        });
    }
};
