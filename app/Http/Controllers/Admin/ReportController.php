<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Inventory\Models\Lot;
use App\Modules\Inventory\Models\Material;
use App\Modules\Production\Models\ProductionStage;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports', [
            'sales' => SalesOrder::query()->selectRaw('status, count(*) as jobs, sum(total) as value')->groupBy('status')->get(),
            'waste' => ProductionStage::query()->selectRaw('name, sum(good_qty) as good, sum(waste_qty) as waste')->groupBy('name')->get(),
            'valuation' => Material::query()->with('lots')->get()->map(fn (Material $material) => [
                'sku' => $material->sku,
                'name' => $material->name,
                'qty' => $material->lots->sum('qty_on_hand'),
                'value' => $material->lots->sum(fn (Lot $lot) => (float) $lot->qty_on_hand * (float) $lot->unit_cost),
            ]),
        ]);
    }

    public function export(Request $request, XlsxWriter $xlsx): StreamedResponse|\Symfony\Component\HttpFoundation\Response
    {
        $rows = Material::query()->with('lots')->get()->map(fn (Material $material) => [
            $material->sku,
            $material->name,
            $material->category,
            $material->lots->sum('qty_on_hand'),
            round($material->lots->sum(fn (Lot $lot) => (float) $lot->qty_on_hand * (float) $lot->unit_cost), 2),
        ])->all();

        if ($request->query('format') === 'csv') {
            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['SKU', 'Material', 'Category', 'On hand', 'Value']);
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
            }, 'inventory-valuation.csv', ['Content-Type' => 'text/csv']);
        }

        return $xlsx->download('inventory-valuation.xlsx', ['SKU', 'Material', 'Category', 'On hand', 'Value'], $rows);
    }
}
