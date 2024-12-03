<?php

namespace App\Services;

use App\Repositories\ColourChangeRepository;
use Carbon\Carbon;

    class ColourChangeService
{
    protected $colourChangeRepository;

    public function __construct(ColourChangeRepository $colourChangeRepository)
    {
        $this->colourChangeRepository = $colourChangeRepository;
    }

    public function getColourChangesForUser(int $userId): array
    {
        // Fetch colour changes from the repository
        $colourChanges = $this->colourChangeRepository->getColourChangesByUserId($userId);

        // Format data
        foreach ($colourChanges as $change) {
            $change->month_display = Carbon::parse($change->start_date)->isoFormat('MMMM Y');
            $change->box_color = $change->color_id === 1 ? 'HIJAU' : ($change->color_id === 2 ? 'MERAH' : 'UNKNOWN');
        }

        return $colourChanges;
    }
}
