<?php

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceRegistry;
use App\Http\Controllers\Controller;
use App\Modules\Crm\Services\SalesOrderService;
use App\Modules\Finance\Services\InvoicingService;
use App\Modules\Finance\Services\PdfDocuments;
use App\Modules\Issues\Services\IssueDiagnosisService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CrudController extends Controller
{
    public function index(Request $request, string $resource): View
    {
        $this->guard($request, $resource);
        $model = ResourceRegistry::find($resource);
        $query = $model::query()->latest('id');
        $columns = $this->columns($model);

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($builder) use ($columns, $search, $model) {
                foreach ($this->searchable($model, $columns) as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return view('admin.resource.index', [
            'resource' => $resource,
            'label' => ResourceRegistry::label($resource),
            'columns' => array_slice($columns, 0, 6),
            'records' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request, string $resource): View
    {
        $this->guard($request, $resource);
        $model = ResourceRegistry::find($resource);

        return view('admin.resource.form', [
            'resource' => $resource,
            'label' => ResourceRegistry::label($resource),
            'fields' => $this->fields($model),
            'record' => new $model,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $this->guard($request, $resource);
        $model = ResourceRegistry::find($resource);
        $data = $this->validateRecord($request, $model);
        if ($model === \App\Models\User::class && empty($data['password'])) {
            $data['password'] = 'password';
        }

        $record = $model::query()->create($data);

        return redirect()->route('admin.resources.show', [$resource, $record->getKey()])->with('status', 'Saved.');
    }

    public function show(Request $request, string $resource, int $id): View
    {
        $this->guard($request, $resource);
        $model = ResourceRegistry::find($resource);
        $record = $model::query()->findOrFail($id);

        return view('admin.resource.show', [
            'resource' => $resource,
            'label' => ResourceRegistry::label($resource),
            'record' => $record,
            'fields' => $this->fields($model, false),
        ]);
    }

    public function edit(Request $request, string $resource, int $id): View
    {
        $this->guard($request, $resource);
        $model = ResourceRegistry::find($resource);

        return view('admin.resource.form', [
            'resource' => $resource,
            'label' => ResourceRegistry::label($resource),
            'fields' => $this->fields($model),
            'record' => $model::query()->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $this->guard($request, $resource);
        $model = ResourceRegistry::find($resource);
        $record = $model::query()->findOrFail($id);
        $record->update($this->validateRecord($request, $model, $record));

        return redirect()->route('admin.resources.show', [$resource, $record->getKey()])->with('status', 'Updated.');
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $this->guard($request, $resource);
        ResourceRegistry::find($resource)::query()->findOrFail($id)->delete();

        return redirect()->route('admin.resources.index', $resource)->with('status', 'Removed.');
    }

    public function confirmOrder(Request $request, int $id, SalesOrderService $orders): RedirectResponse
    {
        $this->guard($request, 'sales-orders');
        $order = \App\Modules\Crm\Models\SalesOrder::query()->findOrFail($id);
        $this->authorize('confirm', $order);
        $orders->confirm($order);

        return back()->with('status', 'Order confirmed. A production order was opened and materials reserved.');
    }

    public function invoiceOrder(Request $request, int $id, InvoicingService $invoicing): RedirectResponse
    {
        $this->guard($request, 'sales-orders');
        $order = \App\Modules\Crm\Models\SalesOrder::query()->with('lines')->findOrFail($id);
        $invoice = $invoicing->fromSalesOrder($order);

        return redirect()->route('admin.resources.show', ['invoices', $invoice->id])->with('status', 'Invoice issued.');
    }

    public function diagnose(Request $request, int $id, IssueDiagnosisService $diagnosis): RedirectResponse
    {
        $this->guard($request, 'issues');
        $issue = \App\Modules\Issues\Models\Issue::query()->findOrFail($id);
        $diagnosis->diagnose($issue);

        return back()->with('status', 'A resolution note is on the issue.');
    }

    public function invoicePdf(int $id, PdfDocuments $pdf)
    {
        $invoice = \App\Modules\Finance\Models\Invoice::query()->findOrFail($id);

        return $pdf->invoice($invoice);
    }

    public function jobCard(int $id, PdfDocuments $pdf)
    {
        $job = \App\Modules\Production\Models\ProductionOrder::query()->findOrFail($id);

        return $pdf->jobCard($job);
    }

    protected function guard(Request $request, string $resource): void
    {
        $module = ResourceRegistry::modules()[$resource] ?? abort(404);
        $user = $request->user();

        if (! $user || ! $user->canAccessModule($module)) {
            abort(403);
        }
    }

    /** @return list<string> */
    protected function columns(string $model): array
    {
        return array_values(array_filter(
            Schema::getColumnListing((new $model)->getTable()),
            fn ($column) => ! in_array($column, ['id', 'password', 'remember_token', 'preferences', 'deleted_at'], true)
        ));
    }

    /** @param  list<string>  $columns
     * @return list<string>
     */
    protected function searchable(string $model, array $columns): array
    {
        return array_values(array_filter($columns, fn ($column) => in_array($column, ['name', 'title', 'number', 'code', 'sku', 'email', 'company', 'lot_no'], true)));
    }

    /** @return list<array{name: string, label: string, type: string, options?: array<int|string, string>}> */
    protected function fields(string $model, bool $editable = true): array
    {
        $instance = new $model;
        $table = $instance->getTable();
        $fields = [];

        foreach ($this->columns($model) as $column) {
            if (in_array($column, ['created_at', 'updated_at', 'email_verified_at'], true)) {
                continue;
            }

            try {
                $dbType = Schema::getColumnType($table, $column);
            } catch (\Throwable) {
                $dbType = 'string';
            }

            if (in_array($dbType, ['json', 'jsonb'], true)) {
                continue;
            }

            $type = $this->inputType($table, $column);
            $field = [
                'name' => $column,
                'label' => str($column)->replace('_', ' ')->title()->toString(),
                'type' => $type,
            ];

            if (str_ends_with($column, '_id')) {
                $field['type'] = 'select';
                $field['options'] = $this->optionsFor($column);
            }

            if ($column === 'role') {
                $field['type'] = 'select';
                $field['options'] = collect(['super_admin', 'factory_manager', 'production_supervisor', 'qc_inspector', 'sales', 'warehouse', 'finance', 'operator', 'customer'])
                    ->mapWithKeys(fn ($role) => [$role => str($role)->replace('_', ' ')->title()->toString()])->all();
            }

            if (in_array($column, ['status', 'kind', 'type', 'category', 'process', 'severity', 'stage', 'result', 'method'], true) && $type === 'text') {
                $field['type'] = 'text';
            }

            $fields[] = $field;
        }

        if ($editable && $model === \App\Models\User::class) {
            $fields[] = ['name' => 'password', 'label' => 'Password', 'type' => 'password'];
        }

        return $fields;
    }

    protected function inputType(string $table, string $column): string
    {
        try {
            $type = Schema::getColumnType($table, $column);
        } catch (\Throwable) {
            $type = 'string';
        }

        return match ($type) {
            'boolean', 'bool' => 'checkbox',
            'text', 'json', 'jsonb' => 'textarea',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime-local',
            'integer', 'bigint', 'smallint', 'decimal', 'float', 'double' => 'number',
            default => 'text',
        };
    }

    /** @return array<int|string, string> */
    protected function optionsFor(string $column): array
    {
        $map = [
            'customer_id' => \App\Modules\Crm\Models\Customer::class,
            'product_id' => \App\Modules\Catalog\Models\Product::class,
            'product_category_id' => \App\Modules\Catalog\Models\ProductCategory::class,
            'material_id' => \App\Modules\Inventory\Models\Material::class,
            'warehouse_id' => \App\Modules\Inventory\Models\Warehouse::class,
            'supplier_id' => \App\Modules\Procurement\Models\Supplier::class,
            'department_id' => \App\Modules\Hr\Models\Department::class,
            'shift_id' => \App\Modules\Hr\Models\Shift::class,
            'user_id' => \App\Models\User::class,
            'owner_id' => \App\Models\User::class,
            'operator_id' => \App\Models\User::class,
            'assignee_id' => \App\Models\User::class,
            'reporter_id' => \App\Models\User::class,
            'inspector_id' => \App\Models\User::class,
            'machine_id' => \App\Modules\Production\Models\Machine::class,
            'work_center_id' => \App\Modules\Catalog\Models\WorkCenter::class,
            'sales_order_id' => \App\Modules\Crm\Models\SalesOrder::class,
            'quotation_id' => \App\Modules\Crm\Models\Quotation::class,
            'production_order_id' => \App\Modules\Production\Models\ProductionOrder::class,
            'invoice_id' => \App\Modules\Finance\Models\Invoice::class,
            'purchase_order_id' => \App\Modules\Procurement\Models\PurchaseOrder::class,
            'lot_id' => \App\Modules\Inventory\Models\Lot::class,
            'lead_id' => \App\Modules\Crm\Models\Lead::class,
            'opportunity_id' => \App\Modules\Crm\Models\Opportunity::class,
            'bom_id' => \App\Modules\Catalog\Models\Bom::class,
            'routing_id' => \App\Modules\Catalog\Models\Routing::class,
            'recipe_id' => \App\Modules\Catalog\Models\Recipe::class,
            'inspection_id' => \App\Modules\Quality\Models\Inspection::class,
            'defect_id' => \App\Modules\Quality\Models\Defect::class,
            'maintenance_plan_id' => \App\Modules\Maintenance\Models\MaintenancePlan::class,
            'approved_by' => \App\Models\User::class,
        ];

        $class = $map[$column] ?? null;

        if ($class === null) {
            return [];
        }

        $label = match (true) {
            Schema::hasColumn((new $class)->getTable(), 'name') => 'name',
            Schema::hasColumn((new $class)->getTable(), 'number') => 'number',
            Schema::hasColumn((new $class)->getTable(), 'title') => 'title',
            Schema::hasColumn((new $class)->getTable(), 'lot_no') => 'lot_no',
            default => 'id',
        };

        return $class::query()->orderBy($label)->limit(200)->pluck($label, 'id')->all();
    }

    /** @return array<string, mixed> */
    protected function validateRecord(Request $request, string $model, ?Model $existing = null): array
    {
        $rules = [];

        foreach ($this->fields($model) as $field) {
            $name = $field['name'];
            if ($name === 'password') {
                $rules['password'] = $existing ? 'nullable|string|min:8' : 'nullable|string|min:8';
                continue;
            }
            $rules[$name] = $field['type'] === 'checkbox' ? 'nullable|boolean' : 'nullable';
        }

        $data = $request->validate($rules);

        foreach ($this->fields($model) as $field) {
            if ($field['type'] === 'checkbox') {
                $data[$field['name']] = $request->boolean($field['name']);
            }
        }

        if (array_key_exists('password', $data) && ($data['password'] === null || $data['password'] === '')) {
            unset($data['password']);
        }

        return $data;
    }
}
