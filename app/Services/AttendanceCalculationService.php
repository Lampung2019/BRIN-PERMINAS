<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceCalculationRule;
use Carbon\Carbon;

class AttendanceCalculationService
{
    /**
     * Menghitung jam kerja berdasarkan aturan
     * yang berlaku pada tanggal presensi.
     */
    public function calculate(Attendance $attendance): array
    {
        if (!$attendance->clock_in || !$attendance->clock_out) {
            return [
                'actual_minutes' => 0,
                'normal_minutes' => 0,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
            ];
        }

        $clockIn = Carbon::parse(
            $attendance->date->format('Y-m-d') . ' ' . $attendance->clock_in
        );

        $clockOut = Carbon::parse(
            $attendance->date->format('Y-m-d') . ' ' . $attendance->clock_out
        );

        // Jika clock-out melewati tengah malam.
        if ($clockOut->lessThan($clockIn)) {
            $clockOut->addDay();
        }

        $actualMinutes = $clockIn->diffInMinutes($clockOut);

        $rule = $this->findRule($attendance->date);

        if (!$rule) {
            return [
                'actual_minutes' => $actualMinutes,
                'normal_minutes' => $actualMinutes,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
            ];
        }

        $lateMinutes = $this->calculateLateMinutes(
            $attendance,
            $rule
        );

        $normalMinutes = $this->calculateNormalMinutes(
            $actualMinutes,
            $lateMinutes,
            $rule
        );

        $overtimeMinutes = max(
             0,
            $actualMinutes - $normalMinutes
        );

        return [
            'actual_minutes' => $actualMinutes,
            'normal_minutes' => $normalMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'late_minutes' => $lateMinutes,
        ];
    }

    /**
     * Mencari aturan berdasarkan tanggal presensi.
     */
    protected function findRule($date): ?AttendanceCalculationRule
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        return AttendanceCalculationRule::query()
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * Menghitung keterlambatan.
     */
    protected function calculateLateMinutes(
        Attendance $attendance,
        AttendanceCalculationRule $rule
    ): int {
        if (!$attendance->clock_in) {
            return 0;
        }

        /*
         * Untuk sementara jadwal standar BRIN:
         * 08:00.
         *
         * Nanti akan kita sambungkan dengan
         * WorkSchedule / EmployeeShift.
         */
        $scheduledTime = '08:00:00';

        $clockIn = Carbon::parse(
            $attendance->date->format('Y-m-d') . ' ' . $attendance->clock_in
        );

        $scheduled = Carbon::parse(
            $attendance->date->format('Y-m-d') . ' ' . $scheduledTime
        );

        $lateLimit = $scheduled->copy()
            ->addMinutes($rule->late_tolerance_minutes);

        if ($clockIn->lessThanOrEqualTo($lateLimit)) {
            return 0;
        }

        return $scheduled->diffInMinutes($clockIn);
    }

    /**
     * Menghitung jam normal berdasarkan mode aturan.
     */
    protected function calculateNormalMinutes(
        int $actualMinutes,
        int $lateMinutes,
        AttendanceCalculationRule $rule
    ): int {
        /*
         * MODE 1:
         * BRIN_ROUNDING
         */
        if ($rule->mode === 'BRIN_ROUNDING') {

            // Jika terlambat, langsung dihitung
            // sesuai nilai normal_minutes_if_late.
            if ($lateMinutes > 0) {
                return $rule->normal_minutes_if_late;
            }

            /*
             * Tidak terlambat:
             *
             * >= 8 jam 45 menit
             * menjadi 9 jam.
             */
            if ($actualMinutes >= $rule->rounding_threshold_minutes) {
                return 540;
            }

            /*
             * Kurang dari 8 jam 45 menit:
             * gunakan waktu aktual.
             */
            return $actualMinutes;
        }

        /*
         * MODE 2:
         * ACTUAL_TIME
         *
         * Semua waktu dihitung sesuai
         * durasi aktual.
         */
        if ($rule->mode === 'ACTUAL_TIME') {
            return $actualMinutes;
        }

        /*
         * Kalau mode tidak dikenal,
         * lebih aman menggunakan waktu aktual.
         */
        return $actualMinutes;
    }
}