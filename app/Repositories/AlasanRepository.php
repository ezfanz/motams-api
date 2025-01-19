<?php

namespace App\Repositories;

use App\Models\Alasan;

class AlasanRepository
{
    /**
     * Get the list of Alasan based on filters.
     */
    public function getAlasanList(array $filters)
    {
        $query = Alasan::where('is_deleted', 0);

        if (isset($filters['late'])) {
            $query->where('late', $filters['late']);
        }

        if (isset($filters['early'])) {
            $query->where('early', $filters['early']);
        }

        if (isset($filters['absent'])) {
            $query->where('absent', $filters['absent']);
        }

        return $query->orderBy('diskripsi', 'asc')->get();
    }
}
