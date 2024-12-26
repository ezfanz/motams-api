<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Department extends Model
{
    use HasFactory;

    protected $table = 'department';

    protected $fillable = [
        'diskripsi', 'email', 'is_deleted', 'id_pencipta', 'pengguna',
    ];

    public function activeDepartments()
    {
        return $this->hasMany(ActiveDepartment::class, 'department_id');
    }
}
