<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employee';

    protected $fillable = [
        'sbiid', 'name', 'surname', 'nric', 'card_number', 'commencementdatetime', 'expirydatetime', 'lastmodifdatetime', 'is_deleted', 'id_pencipta', 'pengguna',
    ];
}