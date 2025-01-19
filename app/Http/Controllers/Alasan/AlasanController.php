<?php

namespace App\Http\Controllers\Alasan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AlasanService;
use App\Helpers\ResponseHelper;

class AlasanController extends Controller
{
    protected $alasanService;

    public function __construct(AlasanService $alasanService)
    {
        $this->alasanService = $alasanService;
    }

    /**
     * Retrieve the list of Alasan.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['late', 'early', 'absent']);
            $alasanList = $this->alasanService->getAlasanList($filters);

            return ResponseHelper::success($alasanList, 'Alasan list retrieved successfully.');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
