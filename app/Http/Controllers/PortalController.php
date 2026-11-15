<?php

namespace App\Http\Controllers;

use App\Modules\Crm\Models\SalesOrder;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(): View
    {
        $orders = SalesOrder::query()
            ->with('lines')
            ->when(auth()->user()->customer_id, fn ($q, $id) => $q->where('customer_id', $id))
            ->latest()
            ->get();

        return view('portal.index', ['orders' => $orders]);
    }
}
