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

    public function getUserColourChanges(): JsonResponse
    {
        $userId = Auth::user()->id; // Get the logged-in user ID
        $colourChanges = $this->colourChangeService->getColourChangesForUser($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User colour changes retrieved successfully',
            'data' => $colourChanges,
        ]);
    }
}
