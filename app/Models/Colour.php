<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colour extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'hex_code',
    ];

    public function colorChanges()
    {
        return $this->hasMany(ColorChange::class);
    }
}
