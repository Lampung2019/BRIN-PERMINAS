<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel aturan perhitungan jam kerja.
     */
    public function up(): void
    {
        Schema::create('attendance_calculation_rules', function (Blueprint $table) {
            $table->id();

            // Nama aturan
            $table->string('name');

            // Berlaku mulai tanggal berapa
            $table->date('effective_from');

            // Berlaku sampai tanggal berapa
            // NULL = masih berlaku sampai sekarang
            $table->date('effective_until')->nullable();

            // Mode perhitungan
            // BRIN_ROUNDING = aturan pembulatan BRIN saat ini
            // ACTUAL_TIME   = hitung berdasarkan waktu aktual
            $table->string('mode', 30);

            // Toleransi keterlambatan
            // Contoh: 15 menit
            $table->unsignedInteger('late_tolerance_minutes')->default(15);

            // Batas minimal agar dibulatkan menjadi jam normal
            // 525 menit = 8 jam 45 menit
            $table->unsignedInteger('rounding_threshold_minutes')->default(525);

            // Jika terlambat, jam normal yang dihitung
            // 480 menit = 8 jam
            $table->unsignedInteger('normal_minutes_if_late')->default(480);

            // Apakah aturan sedang aktif
            $table->boolean('is_active')->default(true);

            // Catatan tambahan
            $table->text('description')->nullable();

            $table->timestamps();

            // Membantu pencarian aturan berdasarkan tanggal
            $table->index(['effective_from', 'effective_until']);

            // Membantu pencarian aturan aktif
            $table->index(['is_active', 'effective_from']);
        });
    }

    /**
     * Menghapus tabel jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_calculation_rules');
    }
};