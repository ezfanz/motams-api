<?php

namespace App\Repositories;

use App\Models\ColourChange;

class ColourChangeRepository
{
    public function getColourChangesByUserId(int $userId)
    {
        return ColourChange::select('id', 'start_date', 'colour_id')
            ->where('user_id', $userId)
            ->whereNull('deleted_at') // SoftDeletes ensures only non-deleted records
            ->orderBy('start_date', 'desc')
            ->get()
            ->toArray(); 
    }
}
