<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Production\Models\ProductionStage;
use App\Modules\Production\Services\ProductionPlanner;
use App\Modules\Production\Services\ShopFloorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopFloorController extends Controller
{
    public function index(ProductionPlanner $planner): View
    {
        return view('admin.shop-floor.index', [
            'jobs' => ProductionOrder::query()->with('product', 'stages')->whereIn('status', ['planned', 'released', 'in_progress', 'qc'])->latest()->paginate(20),
            'groups' => $planner->changeoverGroups(),
        ]);
    }

    public function show(ProductionOrder $job): View
    {
        $this->authorize('execute', $job);
        $job->load('stages.machine', 'stages.operator', 'requirements.material', 'product', 'salesOrder.customer');

        return view('admin.shop-floor.show', ['job' => $job]);
    }

    public function complete(Request $request, ProductionStage $stage, ShopFloorService $floor): RedirectResponse
    {
        $data = $request->validate([
            'good_qty' => ['required', 'numeric', 'min:0'],
            'waste_qty' => ['nullable', 'numeric', 'min:0'],
            'downtime_minutes' => ['nullable', 'numeric', 'min:0'],
            'downtime_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $floor->complete($stage, $data);

        return back()->with('status', $stage->name.' recorded. The next centre has been told.');
    }
}
