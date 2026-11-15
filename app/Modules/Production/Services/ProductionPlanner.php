<?php

namespace App\Modules\Production\Services;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\RoutingStep;
use App\Modules\Catalog\Models\WorkCenter;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Crm\Models\SalesOrderLine;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Production\Models\Machine;
use App\Modules\Production\Models\MaterialRequirement;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Production\Models\ProductionStage;
use App\Modules\Production\Models\ScheduleSlot;
use App\Support\DocumentNumber;
use App\Support\FactoryCalendar;
use Illuminate\Support\Facades\DB;
use Tarikh\Tarikh;

class ProductionPlanner
{
    public function __construct(
        protected StockLedger $ledger,
        protected FactoryCalendar $calendar,
    ) {}

    public function fromSalesOrder(SalesOrder $order): ProductionOrder
    {
        return DB::transaction(function () use ($order) {
            $order->load('lines.product.boms.items', 'lines.product.routings.steps.workCenter');
            $first = null;

            foreach ($order->lines as $line) {
                $job = $this->fromLine($order, $line);
                $first ??= $job;
            }

            return $first ?? throw new \RuntimeException('Sales order has no lines to plan.');
        });
    }

    public function fromLine(SalesOrder $order, SalesOrderLine $line): ProductionOrder
    {
        $product = $line->product;
        $job = ProductionOrder::query()->create([
            'number' => DocumentNumber::next('JO'),
            'sales_order_id' => $order->id,
            'sales_order_line_id' => $line->id,
            'product_id' => $product?->id,
            'user_id' => auth()->id(),
            'status' => 'planned',
            'quantity' => $line->quantity,
            'planned_start' => now()->toDateString(),
            'planned_end' => $this->calendar->addWorkingDays(Tarikh::today('Asia/Dhaka'), 5)->format('Y-m-d'),
            'priority' => $order->due_on && $order->due_on->lte(now()->addDays(7)) ? 'rush' : 'normal',
            'group_key' => $this->groupKey($product, $line),
            'notes' => $line->description,
        ]);

        $this->explode($job, $product, (float) $line->quantity);
        $this->route($job, $product, (float) $line->quantity);
        $this->schedule($job);

        return $job->load('stages', 'requirements');
    }

    protected function explode(ProductionOrder $job, ?Product $product, float $qty): void
    {
        $bom = $product?->boms()->where('is_active', true)->with('items')->first();

        if (! $bom) {
            return;
        }

        foreach ($bom->items as $item) {
            $required = $qty * (float) $item->qty_per * (1 + ((float) $item->scrap_percent / 100));
            $result = $this->ledger->reserve($item->material, $required, $job);

            MaterialRequirement::query()->create([
                'production_order_id' => $job->id,
                'material_id' => $item->material_id,
                'required_qty' => round($required, 3),
                'reserved_qty' => $result['reserved'],
                'shortage_qty' => $result['shortage'],
                'uom' => $item->uom,
            ]);
        }
    }

    protected function route(ProductionOrder $job, ?Product $product, float $qty): void
    {
        $routing = $product?->routings()->where('is_active', true)->with('steps')->first();
        $steps = $routing?->steps?->sortBy('sequence');

        if ($steps === null || $steps->isEmpty()) {
            $steps = collect($this->template($product?->type ?? 'corrugated'));
        }

        $sequence = 1;

        foreach ($steps as $step) {
            if ($step instanceof RoutingStep) {
                $name = $step->name;
                $centerId = $step->work_center_id;
            } else {
                $name = $step['name'];
                $centerId = WorkCenter::query()->where('process', $step['process'])->value('id');
            }

            $machineId = $centerId
                ? Machine::query()->where('work_center_id', $centerId)->where('status', '!=', 'down')->value('id')
                : null;

            ProductionStage::query()->create([
                'production_order_id' => $job->id,
                'routing_step_id' => $step instanceof RoutingStep ? $step->id : null,
                'work_center_id' => $centerId,
                'machine_id' => $machineId,
                'sequence' => $sequence,
                'name' => $name,
                'status' => $sequence === 1 ? 'ready' : 'pending',
                'planned_qty' => $qty,
            ]);
            $sequence++;
        }
    }

    /** @return list<array{name: string, process: string}> */
    protected function template(string $type): array
    {
        return match ($type) {
            'flexible' => [
                ['name' => 'Printing', 'process' => 'printing'],
                ['name' => 'Lamination', 'process' => 'lamination'],
                ['name' => 'Slitting', 'process' => 'slitting'],
                ['name' => 'Pouch making', 'process' => 'forming'],
                ['name' => 'Packing', 'process' => 'packing'],
            ],
            'label' => [
                ['name' => 'Printing', 'process' => 'printing'],
                ['name' => 'Finishing', 'process' => 'finishing'],
                ['name' => 'Slitting', 'process' => 'slitting'],
                ['name' => 'Packing', 'process' => 'packing'],
            ],
            'carton' => [
                ['name' => 'Printing', 'process' => 'printing'],
                ['name' => 'Die cutting', 'process' => 'cutting'],
                ['name' => 'Folding and gluing', 'process' => 'gluing'],
                ['name' => 'Packing', 'process' => 'packing'],
            ],
            default => [
                ['name' => 'Printing', 'process' => 'printing'],
                ['name' => 'Die cutting', 'process' => 'cutting'],
                ['name' => 'Folding and gluing', 'process' => 'gluing'],
                ['name' => 'Packing', 'process' => 'packing'],
            ],
        };
    }

    protected function schedule(ProductionOrder $job): void
    {
        $start = Tarikh::parse($job->planned_start?->toDateString() ?? now()->toDateString(), 'Asia/Dhaka');
        $cursor = $start;

        foreach ($job->stages()->orderBy('sequence')->get() as $stage) {
            while (! $this->calendar->isWorkingDay($cursor)) {
                $cursor = $cursor->addDays(1);
            }

            $begin = $cursor->format('Y-m-d').' 08:00:00';
            $end = $cursor->format('Y-m-d').' 17:00:00';

            ScheduleSlot::query()->create([
                'production_order_id' => $job->id,
                'machine_id' => $stage->machine_id,
                'work_center_id' => $stage->work_center_id,
                'starts_at' => $begin,
                'ends_at' => $end,
                'label' => $stage->name,
            ]);

            $cursor = $cursor->addDays(1);
        }
    }

    protected function groupKey(?Product $product, SalesOrderLine $line): string
    {
        $spec = $line->spec ?? [];
        $flute = $product?->flute ?? ($spec['flute'] ?? 'na');
        $colors = $product?->ink_colors ?? ($spec['ink_colors'] ?? 0);
        $type = $product?->type ?? ($spec['product_type'] ?? 'pack');

        return strtolower($type.'|'.$flute.'|'.$colors.'c');
    }

    /** @return array<string, list<string>> */
    public function changeoverGroups(): array
    {
        return ProductionOrder::query()
            ->whereIn('status', ['planned', 'released', 'in_progress'])
            ->whereNotNull('group_key')
            ->orderBy('group_key')
            ->get()
            ->groupBy('group_key')
            ->map(fn ($jobs) => $jobs->pluck('number')->all())
            ->filter(fn ($numbers) => count($numbers) > 1)
            ->all();
    }
}
