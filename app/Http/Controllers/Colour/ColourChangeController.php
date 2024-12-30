<?php

namespace App\Http\Controllers\Colour;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ColourChangeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ColourChangeController extends Controller
{
    protected $colourChangeService;

    public function __construct(ColourChangeService $colourChangeService)
    {
        $this->colourChangeService = $colourChangeService;
    }

    /**
     * Retrieve user colour changes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserColourChanges(Request $request): JsonResponse
    {
        // Get the logged-in user ID or the `idpeg` passed in the request
        $userId = $request->input('idpeg', Auth::user()->id);

        // Fetch colour changes via the service
        $colourChanges = $this->colourChangeService->getColourChangesForUser($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User colour changes retrieved successfully',
            'data' => $colourChanges,
        ]);
    }
}
