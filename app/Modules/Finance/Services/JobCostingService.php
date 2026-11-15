<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\JobCost;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Production\Models\ProductionOrder;

class JobCostingService
{
    public function capture(ProductionOrder $job): JobCost
    {
        $job->load('requirements', 'stages.workCenter', 'salesOrderLine');

        $actualMaterial = (float) StockMovement::query()
            ->where('reference_type', $job->getMorphClass())
            ->where('reference_id', $job->id)
            ->where('type', 'issue')
            ->get()
            ->sum(fn ($move) => abs((float) $move->qty) * (float) $move->unit_cost);

        $actualConversion = 0.0;
        foreach ($job->stages as $stage) {
            if (! $stage->started_at || ! $stage->completed_at) {
                continue;
            }
            $minutes = max(1, $stage->started_at->diffInMinutes($stage->completed_at));
            $rate = (float) ($stage->workCenter->rate_per_minute ?? config('packflow.rates.default_machine_per_minute'));
            $actualConversion += $minutes * $rate;
        }

        $wasteKg = (float) $job->waste_qty;
        $wasteCost = $wasteKg * (float) config('packflow.rates.board_price_per_kg') / 1000;
        $planned = (float) ($job->salesOrderLine->line_total ?? 0);

        return JobCost::query()->updateOrCreate(
            ['production_order_id' => $job->id],
            [
                'planned_material' => round($planned * 0.62, 2),
                'actual_material' => round($actualMaterial, 2),
                'planned_conversion' => round($planned * 0.23, 2),
                'actual_conversion' => round($actualConversion, 2),
                'waste_cost' => round($wasteCost, 2),
                'planned_total' => round($planned, 2),
                'actual_total' => round($actualMaterial + $actualConversion + $wasteCost, 2),
            ]
        );
    }
}
