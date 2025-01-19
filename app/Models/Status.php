<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'diskripsi', 'is_deleted', 'id_pencipta', 'pengguna',
    ];

    /**
     * Relationship with ReasonTransactions.
     * This assumes you’ve added `status_id` to the `reason_transactions` table.
     */
    public function reasonTransactions()
    {
        return $this->hasMany(ReasonTransaction::class, 'status_id');
    }

    /**
     * Relationship with OfficeLeaveRequest.
     */
    public function officeLeaveRequests()
    {
        return $this->hasMany(OfficeLeaveRequest::class, 'status', 'id');
    }
}
