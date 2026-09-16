<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
    }

    /**
     * Get dashboard summary statistics.
     */
    public function index(): JsonResponse
    {
        $stats = $this->dashboardService->getSummaryStats();

        return response()->json([
            'data' => $stats,
        ]);
    }
}