<?php

namespace App\Repositories;

use App\Models\Status;

class StatusRepository
{
    /**
     * Get all statuses from the database.
     */
    public function getAllStatuses()
    {
        return Status::all();
    }
}
