<?php

namespace App\Services;

use App\Repositories\ColourChangeRepository;
use Carbon\Carbon;
use App\Models\PenukaranWarna;

    class ColourChangeService
{
    protected $colourChangeRepository;

    public function __construct(ColourChangeRepository $colourChangeRepository)
    {
        $this->colourChangeRepository = $colourChangeRepository;
    }

   /**
     * Fetch colour changes for a user by ID.
     *
     * @param int $userId
     * @return array
     */
    public function getColourChangesForUser(int $userId): array
    {
        // Query PenukaranWarna model for the user's colour changes
        $colourChanges = PenukaranWarna::select(
                'penukaranwarna.id',
                'penukaranwarna.tarikhdari',
                'penukaranwarna.warna'
            )
            ->where('penukaranwarna.idpeg', $userId)
            ->where('penukaranwarna.is_deleted', '!=', 1)
            ->orderBy('penukaranwarna.tarikhdari', 'desc')
            ->get();

        // Format data with additional fields
        foreach ($colourChanges as $change) {
            $change->month_display = Carbon::parse($change->tarikhdari)->isoFormat('MMMM Y');
            $change->box_color = $change->warna == 1 ? 'HIJAU' : ($change->warna == 2 ? 'MERAH' : 'UNKNOWN');
        }

        return $colourChanges->toArray();
    }
}
