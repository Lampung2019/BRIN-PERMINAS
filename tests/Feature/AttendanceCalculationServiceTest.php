<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCalculationRule;
use App\Services\AttendanceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AttendanceCalculationRule::create([
            'name' => 'BRIN - Oktober 2026',
            'effective_from' => '2026-10-01',
            'effective_until' => '2026-10-31',
            'mode' => 'BRIN_ROUNDING',
            'late_tolerance_minutes' => 15,
            'rounding_threshold_minutes' => 525,
            'normal_minutes_if_late' => 480,
            'is_active' => true,
        ]);
    }

    private function makeAttendance(
        string $clockIn,
        string $clockOut
    ): Attendance {
        return new Attendance([
            'date' => '2026-10-04',
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);
    }

    public function test_08_00_sampai_17_00_dihitung_9_jam(): void
    {
        $attendance = $this->makeAttendance('08:00:00', '17:00:00');

        $result = app(AttendanceCalculationService::class)
            ->calculate($attendance);

        $this->assertEquals(540, $result['actual_minutes']);
        $this->assertEquals(540, $result['normal_minutes']);
        $this->assertEquals(0, $result['late_minutes']);
    }

    public function test_08_15_sampai_17_15_dihitung_9_jam(): void
    {
        $attendance = $this->makeAttendance('08:15:00', '17:15:00');

        $result = app(AttendanceCalculationService::class)
            ->calculate($attendance);

        $this->assertEquals(540, $result['actual_minutes']);
        $this->assertEquals(540, $result['normal_minutes']);
        $this->assertEquals(0, $result['late_minutes']);
    }

    public function test_08_16_sampai_17_16_dihitung_8_jam(): void
    {
        $attendance = $this->makeAttendance('08:16:00', '17:16:00');

        $result = app(AttendanceCalculationService::class)
            ->calculate($attendance);

        $this->assertEquals(540, $result['actual_minutes']);
        $this->assertEquals(480, $result['normal_minutes']);
        $this->assertEquals(16, $result['late_minutes']);
    }

    public function test_08_00_sampai_16_30_dihitung_8_jam_30_menit(): void
    {
        $attendance = $this->makeAttendance('08:00:00', '16:30:00');

        $result = app(AttendanceCalculationService::class)
            ->calculate($attendance);

        $this->assertEquals(510, $result['actual_minutes']);
        $this->assertEquals(510, $result['normal_minutes']);
        $this->assertEquals(0, $result['late_minutes']);
    }

    public function test_08_00_sampai_17_30_tanpa_lembur_tetap_9_jam(): void
    {
        $attendance = $this->makeAttendance('08:00:00', '17:30:00');

        $result = app(AttendanceCalculationService::class)
            ->calculate($attendance);

        $this->assertEquals(570, $result['actual_minutes']);
        $this->assertEquals(540, $result['normal_minutes']);
        $this->assertEquals(0, $result['overtime_minutes']);
    }
}