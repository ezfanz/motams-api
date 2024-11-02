<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AttendanceStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_type',
    ];

    /**
     * Get the attendance records associated with this status.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
