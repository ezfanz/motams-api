<?php

namespace App\Repositories;

use App\Models\ReviewStatus;

class ReviewStatusRepository
{
    /**
     * Retrieve all review statuses.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return ReviewStatus::all();
    }
}
