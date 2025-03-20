<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Services\StatusService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class StatusController extends Controller
{

    protected $statusService;

    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Display a listing of the statuses based on user role.
     */
    public function index(): JsonResponse
    {
        // Get authenticated user's role ID
        $userRoleId = Auth::user()->role_id ?? null;

        // Fetch filtered statuses based on the role
        $statuses = $this->statusService->getStatusesByRole($userRoleId);

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


     /**
     * Get statuses for Semakan (Review).
     */
    public function getSemakanStatuses(): JsonResponse
    {
        $statuses = $this->statusService->getSemakanStatuses();
        return response()->json([
            'status' => 'success',
            'message' => 'Semakan statuses retrieved successfully',
            'data' => $statuses
        ]);
    }

    /**
     * Get statuses for Pengesahan (Approval).
     */
    public function getPengesahanStatuses(): JsonResponse
    {
        $statuses = $this->statusService->getPengesahanStatuses();
        return response()->json([
            'status' => 'success',
            'message' => 'Pengesahan statuses retrieved successfully',
            'data' => $statuses
        ]);
    }
}
