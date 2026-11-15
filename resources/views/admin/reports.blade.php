@extends('admin-panel::layouts.app')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Reports</h1></div>
    <div class="page-actions">
        <x-admin-panel::button href="{{ route('admin.reports.export', ['format' => 'xlsx']) }}" variant="secondary">Excel</x-admin-panel::button>
        <x-admin-panel::button href="{{ route('admin.reports.export', ['format' => 'csv']) }}" variant="outline">CSV</x-admin-panel::button>
    </div>
</div>
<div class="admin-card" style="padding:1rem;margin-bottom:1rem;">
    <h2 class="admin-card-title">Sales orders</h2>
    @foreach ($sales as $row)<p>{{ $row->status }}: {{ $row->jobs }} orders, {{ number_format($row->value, 0) }}</p>@endforeach
</div>
<div class="admin-card" style="padding:1rem;margin-bottom:1rem;">
    <h2 class="admin-card-title">Yield by stage</h2>
    @foreach ($waste as $row)<p>{{ $row->name }}: good {{ $row->good }}, waste {{ $row->waste }}</p>@endforeach
</div>
<div class="admin-card" style="padding:1rem;">
    <h2 class="admin-card-title">Inventory valuation</h2>
    @foreach ($valuation as $row)<p>{{ $row['sku'] }} {{ $row['name'] }} — {{ $row['qty'] }} / {{ number_format($row['value'], 0) }}</p>@endforeach
</div>
@endsection
