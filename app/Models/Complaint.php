<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'day',
        'complaint_type',
        'complaint_title',
        'officer_notes',
        'status',
        'submitted_by',
        'handled_by'
    ];

    /**
     * Relationship to the user who submitted the complaint.
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relationship to the officer handling the complaint.
     */
    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Polymorphic relationship with ReasonTransaction.
     */
    public function reasonTransactions()
    {
        return $this->morphMany(ReasonTransaction::class, 'reasonable');
    }
}
