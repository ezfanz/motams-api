<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeLeaveRequest extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_by',
        'leave_type_id',
        'date',
        'day',
        'start_time',
        'end_time',
        'reason',
        'status',
        'approval_status_id',
        'approval_notes'
    ];

    /**
     * Define the relationship with the User (creator of the leave request).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Define the relationship with the LeaveType.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvalStatus()
    {
        return $this->belongsTo(ReviewStatus::class, 'approval_status_id');
    }
}
