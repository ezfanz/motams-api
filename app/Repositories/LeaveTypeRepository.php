<?php

namespace App\Repositories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeRepository
{
    /**
     * Get all leave types.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return LeaveType::all();
    }
}
