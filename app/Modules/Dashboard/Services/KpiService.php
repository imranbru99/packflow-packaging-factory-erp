<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Inventory\Models\Material;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Maintenance\Models\DowntimeEvent;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Production\Models\ProductionStage;
use App\Modules\Quality\Models\Defect;
use Illuminate\Support\Carbon;

class KpiService
{
    public function __construct(protected StockLedger $ledger) {}

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $openOrders = SalesOrder::query()->whereNotIn('status', ['invoiced', 'closed', 'cancelled'])->count();
        $jobs = ProductionOrder::query()->whereIn('status', ['planned', 'released', 'in_progress', 'qc']);
        $good = (float) ProductionStage::query()->where('status', 'done')->where('completed_at', '>=', now()->subDays(7))->sum('good_qty');
        $waste = (float) ProductionStage::query()->where('status', 'done')->where('completed_at', '>=', now()->subDays(7))->sum('waste_qty');
        $downtime = (float) DowntimeEvent::query()->where('created_at', '>=', now()->subDays(7))->sum('minutes');
        $plannedMinutes = max(1, ProductionStage::query()->where('status', 'done')->where('completed_at', '>=', now()->subDays(7))->count() * 480);
        $availability = max(0, min(1, ($plannedMinutes - $downtime) / $plannedMinutes));
        $quality = ($good + $waste) > 0 ? $good / ($good + $waste) : 1;
        $performance = 0.86;
        $oee = round($availability * $performance * $quality * 100, 1);

        $alerts = Material::query()->where('is_active', true)->where('reorder_level', '>', 0)->get()
            ->filter(fn (Material $material) => $this->ledger->available($material) < (float) $material->reorder_level)
            ->take(6)
            ->map(fn (Material $material) => [
                'sku' => $material->sku,
                'name' => $material->name,
                'available' => $this->ledger->available($material),
                'reorder' => (float) $material->reorder_level,
            ])->values();

        $revenue = (float) Invoice::query()->where('issued_on', '>=', now()->startOfMonth())->sum('total');
        $rejectRate = ($good + $waste) > 0 ? round($waste / ($good + $waste) * 100, 1) : 0;

        $days = collect(range(6, 0))->map(function (int $ago) {
            $day = Carbon::today()->subDays($ago);

            return [
                'label' => $day->format('D'),
                'good' => (float) ProductionStage::query()->whereDate('completed_at', $day)->sum('good_qty'),
                'plan' => (float) ProductionStage::query()->whereDate('created_at', $day)->sum('planned_qty'),
            ];
        });

        return [
            'open_orders' => $openOrders,
            'jobs_live' => (clone $jobs)->count(),
            'overdue' => SalesOrder::query()->whereNotNull('due_on')->whereDate('due_on', '<', today())->whereNotIn('status', ['invoiced', 'closed', 'cancelled'])->count(),
            'revenue' => $revenue,
            'waste_percent' => ($good + $waste) > 0 ? round($waste / ($good + $waste) * 100, 1) : 0,
            'reject_rate' => $rejectRate,
            'oee' => $oee,
            'alerts' => $alerts,
            'series' => $days,
            'defects' => Defect::query()->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }
}
