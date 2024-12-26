<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransAlasan extends Model
{
    use HasFactory;

    protected $table = 'trans_alasan';

    protected $fillable = [
        'diskripsi', 'created_at', 'updated_at',
    ];
}
