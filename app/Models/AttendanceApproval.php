<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceApproval extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_record_id',
        'reviewed_by',
        'approved_by',
        'review_notes',
        'approval_notes',
        'review_status',
    ];

    /**
     * Relationship with AttendanceRecord.
     */
    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Relationship with User (Reviewer).
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Relationship with User (Approver).
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
