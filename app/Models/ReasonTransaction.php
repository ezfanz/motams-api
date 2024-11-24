<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReasonTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'log_timestamp',
        'reason_id',
        'reason_type_id',
        'employee_notes',
        'employee_reason_by',
        'employee_reason_at',
        'reviewed_by',
        'review_status',
        'review_notes',
        'reviewed_at',
        'approved_by',
        'approval_status',
        'approval_notes',
        'approved_at',
        'created_by',
        'related_user_id',
        'status',
        'reasonable_type',
        'reasonable_id',
    ];

    /**
     * Define the polymorphic relationship.
     */
    public function reasonable()
    {
        return $this->morphTo();
    }

    /**
     * Relationship with User (Employee).
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
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

    /**
     * Relationship with User (Creator).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
