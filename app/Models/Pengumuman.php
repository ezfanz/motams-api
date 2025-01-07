<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'butiran',
        'tkhmula',
        'tkhtamat',
        'department_id',
        'is_deleted',
        'id_pencipta',
        'pengguna',
        'created_at',
        'updated_at',
    ];
}
