<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom perhitungan jam kerja.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Durasi aktual dari clock_in sampai clock_out
            $table->unsignedInteger('actual_minutes')
                ->default(0)
                ->after('clock_out');

            // Jam kerja normal yang diakui sistem
            $table->unsignedInteger('normal_minutes')
                ->default(0)
                ->after('actual_minutes');

            // Lembur yang benar-benar disetujui
            $table->unsignedInteger('overtime_minutes')
                ->default(0)
                ->after('normal_minutes');

            // Jumlah menit keterlambatan
            $table->unsignedInteger('late_minutes')
                ->default(0)
                ->after('overtime_minutes');
        });
    }

    /**
     * Hapus kembali kolom jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'actual_minutes',
                'normal_minutes',
                'overtime_minutes',
                'late_minutes',
            ]);
        });
    }
};