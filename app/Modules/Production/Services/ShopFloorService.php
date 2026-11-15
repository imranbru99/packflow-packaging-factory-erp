<?php

namespace App\Modules\Production\Services;

use App\Modules\Finance\Services\JobCostingService;
use App\Modules\Inventory\Models\FinishedStock;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Production\Events\StageCompleted;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Production\Models\ProductionStage;
use App\Modules\Quality\Models\Inspection;
use App\Modules\Quality\Models\InspectionItem;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;

class ShopFloorService
{
    public function __construct(
        protected StockLedger $ledger,
        protected JobCostingService $costing,
    ) {}

    public function start(ProductionStage $stage, ?int $operatorId = null): ProductionStage
    {
        $stage->status = 'running';
        $stage->operator_id = $operatorId ?? auth()->id();
        $stage->started_at = now();
        $stage->save();

        $stage->productionOrder->update(['status' => 'in_progress']);

        if ($stage->sequence === 1) {
            $this->issueMaterials($stage->productionOrder);
        }

        return $stage;
    }

    /**
     * @param  array{good_qty: float|int, waste_qty?: float|int, downtime_minutes?: float|int, downtime_reason?: ?string, notes?: ?string}  $actual
     */
    public function complete(ProductionStage $stage, array $actual): ProductionStage
    {
        return DB::transaction(function () use ($stage, $actual) {
            if (in_array($stage->status, ['pending', 'ready'], true)) {
                $this->start($stage);
                $stage->refresh();
            }

            $stage->good_qty = $actual['good_qty'];
            $stage->waste_qty = $actual['waste_qty'] ?? 0;
            $stage->downtime_minutes = $actual['downtime_minutes'] ?? 0;
            $stage->downtime_reason = $actual['downtime_reason'] ?? null;
            $stage->notes = $actual['notes'] ?? $stage->notes;
            $stage->status = 'done';
            $stage->completed_at = now();
            $stage->operator_id = $stage->operator_id ?? auth()->id();
            $stage->save();

            if ((float) $stage->downtime_minutes > 0 && $stage->machine_id) {
                $stage->machine->downtimeEvents()->create([
                    'production_stage_id' => $stage->id,
                    'user_id' => $stage->operator_id,
                    'reason' => $stage->downtime_reason ?: 'Unspecified',
                    'minutes' => $stage->downtime_minutes,
                    'started_at' => $stage->completed_at,
                ]);
            }

            $next = $stage->productionOrder->stages()
                ->where('sequence', '>', $stage->sequence)
                ->orderBy('sequence')
                ->first();

            if ($next) {
                $next->status = 'ready';
                $next->planned_qty = $stage->good_qty;
                $next->save();
            } else {
                $this->closeJob($stage);
            }

            event(new StageCompleted($stage, $next));

            return $stage->fresh(['productionOrder.stages']);
        });
    }

    public function completeAll(ProductionOrder $job): ProductionOrder
    {
        $job->load('stages');

        foreach ($job->stages()->orderBy('sequence')->get() as $stage) {
            $this->complete($stage, [
                'good_qty' => (float) $job->quantity,
                'waste_qty' => 0,
            ]);
        }

        return $job->fresh();
    }

    protected function issueMaterials(ProductionOrder $job): void
    {
        $job->load('requirements.material');

        foreach ($job->requirements as $requirement) {
            if ((float) $requirement->reserved_qty <= 0 || (float) $requirement->issued_qty > 0) {
                continue;
            }

            $issued = $this->ledger->issueReserved($requirement->material, (float) $requirement->reserved_qty, $job);
            $requirement->issued_qty = $issued;
            $requirement->save();
        }
    }

    protected function closeJob(ProductionStage $stage): void
    {
        $job = $stage->productionOrder;
        $job->good_qty = $stage->good_qty;
        $job->waste_qty = $job->stages()->sum('waste_qty');
        $job->status = 'qc';
        $job->save();

        $warehouse = Warehouse::query()->where('kind', 'finished')->value('id')
            ?? Warehouse::query()->value('id');

        if ($job->product_id && $warehouse) {
            $stock = FinishedStock::query()->firstOrCreate(
                ['product_id' => $job->product_id, 'warehouse_id' => $warehouse],
                ['qty' => 0]
            );
            $stock->qty = (float) $stock->qty + (float) $stage->good_qty;
            $stock->save();
        }

        $inspection = Inspection::query()->create([
            'number' => DocumentNumber::next('QC'),
            'production_order_id' => $job->id,
            'kind' => 'final',
            'result' => 'pending',
        ]);

        foreach ([
            ['Dimensions', 'Within dieline tolerance'],
            ['Print quality', 'Register and density'],
            ['Bond / adhesion', 'No delamination'],
            ['Count', 'Matches good quantity'],
        ] as [$checkpoint, $spec]) {
            InspectionItem::query()->create([
                'inspection_id' => $inspection->id,
                'checkpoint' => $checkpoint,
                'spec' => $spec,
                'result' => 'pending',
            ]);
        }

        $this->costing->capture($job);
    }
}
