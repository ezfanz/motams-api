<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_record_id',
        'check_in_time',
        'check_out_time',
        'notes',
    ];

    /**
     * Relationship with AttendanceRecord.
     */
    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
