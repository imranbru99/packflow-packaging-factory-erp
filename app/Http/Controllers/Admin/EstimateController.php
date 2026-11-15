<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\Quotation;
use App\Modules\Crm\Models\QuotationLine;
use App\Modules\Crm\Services\OrderOcrService;
use App\Modules\Crm\Services\PackagingEstimator;
use App\Modules\Crm\Services\QuotationService;
use App\Support\DocumentNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstimateController extends Controller
{
    public function create(): View
    {
        return view('admin.estimate', ['estimate' => null, 'parsed' => null]);
    }

    public function store(Request $request, PackagingEstimator $estimator, OrderOcrService $ocr, QuotationService $quotations): RedirectResponse|View
    {
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('ocr');
            $parsed = $ocr->fromFile(storage_path('app/'.$path));
        } elseif ($request->filled('raw_text')) {
            $parsed = $ocr->fromText($request->string('raw_text')->toString());
        } else {
            $parsed = $request->validate([
                'product_type' => ['required', 'in:corrugated,flexible,label,carton'],
                'description' => ['required', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'length_mm' => ['nullable', 'numeric'],
                'width_mm' => ['nullable', 'numeric'],
                'height_mm' => ['nullable', 'numeric'],
                'gusset_mm' => ['nullable', 'numeric'],
                'gsm' => ['nullable', 'numeric'],
                'flute' => ['nullable', 'string'],
                'film_structure' => ['nullable', 'string'],
                'ink_colors' => ['nullable', 'integer'],
                'waste_percent' => ['nullable', 'numeric'],
                'margin_percent' => ['nullable', 'numeric'],
                'tooling_cost' => ['nullable', 'numeric'],
                'customer_id' => ['nullable', 'integer'],
            ]);
        }

        $estimate = $estimator->estimate($parsed);

        if ($request->boolean('save')) {
            $quote = Quotation::query()->create([
                'number' => DocumentNumber::next('QT'),
                'customer_id' => $parsed['customer_id'] ?? null,
                'user_id' => $request->user()->id,
                'status' => 'draft',
                'margin_percent' => $estimate['margin_percent'],
                'valid_until' => now()->addDays(15)->toDateString(),
                'ai_notes' => ['flags' => $estimate['flags'], 'source' => $parsed['source'] ?? 'form'],
            ]);
            $line = new QuotationLine(['quotation_id' => $quote->id]);
            $quotations->priceLine($line, array_merge($parsed, ['description' => $parsed['description'] ?? 'Estimated pack']));

            return redirect()->route('admin.resources.show', ['quotations', $quote->id])->with('status', 'Quotation priced.');
        }

        return view('admin.estimate', ['estimate' => $estimate, 'parsed' => $parsed]);
    }
}
