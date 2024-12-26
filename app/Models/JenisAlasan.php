<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisAlasan extends Model
{
    use HasFactory;

    protected $table = 'jenis_alasan';

    protected $fillable = [
        'diskripsi', 'diskripsi_bm', 'is_deleted', 'id_pencipta', 'pengguna',
    ];
}
