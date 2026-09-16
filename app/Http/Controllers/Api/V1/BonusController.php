<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBonusRequest;
use App\Http\Resources\Api\V1\BonusResource;
use App\Jobs\ProcessBonusDistributionJob;
use App\Models\Bonus;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BonusController extends Controller
{
    /**
     * Display a listing of recorded bonuses.
     */
    public function index(): AnonymousResourceCollection
    {
        $bonuses = Bonus::with(['employee', 'distributions'])->latest()->paginate(15);

        return BonusResource::collection($bonuses);
    }

    /**
     * Store a new bonus and dispatch background distribution logic.
     */
    public function store(StoreBonusRequest $request): BonusResource
    {
        // 1. Persist the bonus record
        $bonus = Bonus::create($request->validated());

        // 2. Dispatch background Job for upline distribution (asynchronous queue execution)
        ProcessBonusDistributionJob::dispatch($bonus);

        // 3. Return Eloquent API Resource (No manual response()->json())
        return new BonusResource($bonus);
    }

    /**
     * Display details of a specific bonus.
     */
    public function show(Bonus $bonus): BonusResource
    {
        return new BonusResource($bonus->load(['employee', 'distributions']));
    }
}