<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReasonType extends Model
{
    use HasFactory;

    protected $fillable = ['description'];

    /**
     * Relationship with ReasonTransaction
     */
    public function reasonTransactions()
    {
        return $this->hasMany(ReasonTransaction::class);
    }
}
