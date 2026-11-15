<?php

namespace App\Modules\Crm\Services;

use App\Modules\Crm\Events\SalesOrderConfirmed;
use App\Modules\Crm\Models\Quotation;
use App\Modules\Crm\Models\QuotationLine;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Crm\Models\SalesOrderLine;
use App\Modules\Settings\Models\CompanySetting;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function __construct(protected PackagingEstimator $estimator) {}

    /** @param  array<string, mixed>  $input */
    public function priceLine(QuotationLine $line, array $input): QuotationLine
    {
        $estimate = $this->estimator->estimate($input);

        $line->fill([
            'description' => $input['description'] ?? $line->description,
            'product_type' => $input['product_type'] ?? 'corrugated',
            'product_id' => $input['product_id'] ?? $line->product_id,
            'length_mm' => $input['length_mm'] ?? null,
            'width_mm' => $input['width_mm'] ?? null,
            'height_mm' => $input['height_mm'] ?? null,
            'gusset_mm' => $input['gusset_mm'] ?? null,
            'quantity' => $estimate['quantity'],
            'gsm' => $estimate['gsm'] ?? null,
            'flute' => $input['flute'] ?? null,
            'film_structure' => $input['film_structure'] ?? null,
            'ink_colors' => $input['ink_colors'] ?? 0,
            'waste_percent' => $estimate['waste_percent'],
            'material_kg' => $estimate['material_kg'],
            'material_cost' => $estimate['material_cost'],
            'machine_minutes' => $estimate['machine_minutes'],
            'machine_cost' => $estimate['machine_cost'],
            'labor_cost' => $estimate['labor_cost'],
            'tooling_cost' => $estimate['tooling_cost'],
            'unit_price' => $estimate['unit_price'],
            'line_total' => $estimate['line_total'],
            'breakdown' => $estimate,
        ]);
        $line->save();

        $this->total($line->quotation);

        return $line;
    }

    public function total(Quotation $quotation): Quotation
    {
        $quotation->load('lines');
        $subtotal = (float) $quotation->lines->sum('line_total');
        $taxPercent = (float) ($quotation->tax_percent ?: CompanySetting::current()->tax_percent);
        $tax = round($subtotal * $taxPercent / 100, 2);
        $quotation->subtotal = $subtotal;
        $quotation->tax_percent = $taxPercent;
        $quotation->tax_amount = $tax;
        $quotation->total = $subtotal + $tax;
        $quotation->save();

        return $quotation;
    }

    public function accept(Quotation $quotation): SalesOrder
    {
        return DB::transaction(function () use ($quotation) {
            $quotation->status = 'accepted';
            $quotation->save();

            $taxPercent = (float) $quotation->tax_percent;
            $order = SalesOrder::query()->create([
                'number' => DocumentNumber::next('SO'),
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'user_id' => auth()->id(),
                'status' => 'draft',
                'ordered_on' => now()->toDateString(),
                'due_on' => now()->addDays(14)->toDateString(),
                'subtotal' => $quotation->subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'artwork_status' => 'pending',
            ]);

            foreach ($quotation->lines as $line) {
                SalesOrderLine::query()->create([
                    'sales_order_id' => $order->id,
                    'product_id' => $line->product_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'line_total' => $line->line_total,
                    'spec' => [
                        'product_type' => $line->product_type,
                        'length_mm' => $line->length_mm,
                        'width_mm' => $line->width_mm,
                        'height_mm' => $line->height_mm,
                        'flute' => $line->flute,
                        'film_structure' => $line->film_structure,
                        'ink_colors' => $line->ink_colors,
                        'gsm' => $line->gsm,
                    ],
                ]);
            }

            return $order->load('lines');
        });
    }
}
