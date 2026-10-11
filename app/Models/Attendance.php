<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'work_mode',
        'notes',
        'latitude',
        'longitude',
        'photo_path',
        'actual_minutes',
        'normal_minutes',
        'overtime_minutes',
        'late_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'actual_minutes' => 'integer',
        'normal_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'late_minutes' => 'integer',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}