<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Services\StatusService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class StatusController extends Controller
{

    protected $statusService;

    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

   /**
     * Display a listing of the statuses.
     */
    public function index(): JsonResponse
    {
        $statuses = $this->statusService->getAllStatuses();
        return response()->json([
            'status' => 'success',
            'message' => 'Statuses retrieved successfully',
            'data' => $statuses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
