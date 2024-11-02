<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];

    /**
     * Define any relationships if needed, for example, with `OfficeLeaveRequest` if you plan to link leave requests to a leave type.
     */
    public function officeLeaveRequests()
    {
        return $this->hasMany(OfficeLeaveRequest::class);
    }
}
