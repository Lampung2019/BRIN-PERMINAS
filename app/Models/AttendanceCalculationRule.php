<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceCalculationRule extends Model
{
    protected $fillable = [
        'name',
        'effective_from',
        'effective_until',
        'mode',
        'late_tolerance_minutes',
        'rounding_threshold_minutes',
        'normal_minutes_if_late',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'late_tolerance_minutes' => 'integer',
            'rounding_threshold_minutes' => 'integer',
            'normal_minutes_if_late' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}