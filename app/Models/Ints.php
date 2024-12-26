<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ints extends Model
{
    use HasFactory;

    protected $table = 'ints';

    protected $fillable = [
        'i', 'is_deleted', 'id_pencipta', 'pengguna', 'created_at', 'updated_at',
    ];
}
