<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Terminal extends Model
{
    use HasFactory;

    protected $table = 'terminal';

    protected $fillable = [
        'vterminal_key', 'description', 'is_deleted', 'id_pencipta', 'pengguna',
    ];
}
