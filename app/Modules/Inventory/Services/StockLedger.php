<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Lot;
use App\Modules\Inventory\Models\Material;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Procurement\Models\PurchaseSuggestion;
use App\Modules\Procurement\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Lot ledger. Issues and reservations walk reels oldest-first,
 * which is how a paper and film store avoids ageing stock.
 */
class StockLedger
{
    public function onHand(Material $material): float
    {
        return (float) $material->lots()->sum('qty_on_hand');
    }

    public function available(Material $material): float
    {
        return (float) $material->lots()->sum(\Illuminate\Support\Facades\DB::raw('qty_on_hand - qty_reserved'));
    }

    public function receive(Material $material, int $warehouseId, string $lotNo, float $qty, float $unitCost, ?Model $reference = null, array $extra = []): Lot
    {
        return DB::transaction(function () use ($material, $warehouseId, $lotNo, $qty, $unitCost, $reference, $extra) {
            $lot = Lot::query()->firstOrCreate(
                ['material_id' => $material->id, 'lot_no' => $lotNo],
                array_merge([
                    'warehouse_id' => $warehouseId,
                    'qty_on_hand' => 0,
                    'qty_reserved' => 0,
                    'unit_cost' => $unitCost,
                    'received_on' => now()->toDateString(),
                    'barcode' => 'PF-'.$material->sku.'-'.$lotNo,
                ], $extra)
            );

            $lot->qty_on_hand = (float) $lot->qty_on_hand + $qty;
            $lot->unit_cost = $unitCost;
            $lot->save();

            $this->move($material, $lot, $warehouseId, 'receipt', $qty, $unitCost, $reference, 'Goods inward');

            return $lot;
        });
    }

    /** @return array{reserved: float, shortage: float} */
    public function reserve(Material $material, float $qty, Model $reference): array
    {
        return DB::transaction(function () use ($material, $qty, $reference) {
            $remaining = $qty;
            $reserved = 0.0;

            $lots = $material->lots()
                ->whereRaw('qty_on_hand > qty_reserved')
                ->orderBy('received_on')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($remaining <= 0) {
                    break;
                }

                $free = (float) $lot->qty_on_hand - (float) $lot->qty_reserved;
                $take = min($free, $remaining);
                $lot->qty_reserved = (float) $lot->qty_reserved + $take;
                $lot->save();
                $this->move($material, $lot, $lot->warehouse_id, 'reserve', $take, (float) $lot->unit_cost, $reference, 'Reserved for production');
                $reserved += $take;
                $remaining -= $take;
            }

            if ($remaining > 0) {
                $this->suggestPurchase($material, $remaining);
            }

            return ['reserved' => round($reserved, 3), 'shortage' => round(max(0, $remaining), 3)];
        });
    }

    public function issueReserved(Material $material, float $qty, Model $reference): float
    {
        return DB::transaction(function () use ($material, $qty, $reference) {
            $remaining = $qty;
            $issued = 0.0;

            $lots = $material->lots()
                ->where('qty_reserved', '>', 0)
                ->orderBy('received_on')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min((float) $lot->qty_reserved, (float) $lot->qty_on_hand, $remaining);
                $lot->qty_reserved = (float) $lot->qty_reserved - $take;
                $lot->qty_on_hand = (float) $lot->qty_on_hand - $take;
                $lot->save();
                $this->move($material, $lot, $lot->warehouse_id, 'issue', -$take, (float) $lot->unit_cost, $reference, 'Issued to the floor');
                $issued += $take;
                $remaining -= $take;
            }

            return round($issued, 3);
        });
    }

    public function suggestPurchase(Material $material, float $qty): PurchaseSuggestion
    {
        $open = PurchaseSuggestion::query()
            ->where('material_id', $material->id)
            ->where('status', 'open')
            ->first();

        if ($open) {
            $open->suggested_qty = max((float) $open->suggested_qty, $qty);
            $open->save();

            return $open;
        }

        return PurchaseSuggestion::query()->create([
            'material_id' => $material->id,
            'supplier_id' => Supplier::query()->where('category', $material->category)->value('id'),
            'suggested_qty' => $qty,
            'reason' => 'Available stock is below the job requirement or reorder level.',
            'status' => 'open',
        ]);
    }

    public function scanReorderLevels(): int
    {
        $count = 0;

        Material::query()->where('is_active', true)->where('reorder_level', '>', 0)->each(function (Material $material) use (&$count) {
            if ($this->available($material) < (float) $material->reorder_level) {
                $this->suggestPurchase($material, (float) $material->reorder_level - $this->available($material));
                $count++;
            }
        });

        return $count;
    }

    protected function move(Material $material, ?Lot $lot, int $warehouseId, string $type, float $qty, float $unitCost, ?Model $reference, string $notes): void
    {
        StockMovement::query()->create([
            'material_id' => $material->id,
            'lot_id' => $lot?->id,
            'warehouse_id' => $warehouseId,
            'type' => $type,
            'qty' => $qty,
            'unit_cost' => $unitCost,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);
    }
}
