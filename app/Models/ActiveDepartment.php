<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveDepartment extends Model
{
    use HasFactory;

    protected $table = 'active_departments';

    protected $fillable = [
        'idpeg', 'staffid', 'datestarts', 'dateends', 'department_id', 'is_deleted', 'id_pencipta', 'pengguna',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'id_pencipta');
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'pengguna');
    }
}