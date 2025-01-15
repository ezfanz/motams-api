<?php

namespace App\Services;

use App\Repositories\AlasanRepository;

class AlasanService
{
    protected $alasanRepository;

    public function __construct(AlasanRepository $alasanRepository)
    {
        $this->alasanRepository = $alasanRepository;
    }

    /**
     * Get the list of Alasan based on filters.
     */
    public function getAlasanList(array $filters)
    {
        return $this->alasanRepository->getAlasanList($filters);
    }
}
