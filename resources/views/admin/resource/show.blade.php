@extends('admin-panel::layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $record->name ?? $record->number ?? $record->title ?? $record->code ?? ('#'.$record->id) }}</h1>
        <p class="page-subtitle">{{ $label }}</p>
    </div>
    <div class="page-actions">
        <x-admin-panel::button href="{{ route('admin.resources.edit', [$resource, $record->id]) }}" variant="secondary">Edit</x-admin-panel::button>
        @if ($resource === 'sales-orders' && $record->status === 'draft')
            <form method="post" action="{{ route('admin.orders.confirm', $record->id) }}">@csrf<button class="admin-btn admin-btn-primary" type="submit">Confirm & plan</button></form>
        @endif
        @if ($resource === 'sales-orders' && in_array($record->status, ['confirmed', 'invoiced']))
            <form method="post" action="{{ route('admin.orders.invoice', $record->id) }}">@csrf<button class="admin-btn admin-btn-secondary" type="submit">Invoice</button></form>
        @endif
        @if ($resource === 'invoices')
            <x-admin-panel::button href="{{ route('admin.invoices.pdf', $record->id) }}" variant="outline">PDF</x-admin-panel::button>
        @endif
        @if ($resource === 'production-orders')
            <x-admin-panel::button href="{{ route('admin.shop-floor.show', $record->id) }}">Run job</x-admin-panel::button>
            <x-admin-panel::button href="{{ route('admin.jobs.pdf', $record->id) }}" variant="outline">Job card</x-admin-panel::button>
        @endif
        @if ($resource === 'issues')
            <form method="post" action="{{ route('admin.issues.diagnose', $record->id) }}">@csrf<button class="admin-btn admin-btn-secondary" type="submit">Ask for a fix</button></form>
        @endif
    </div>
</div>
@if (session('status'))<div class="admin-card" style="padding:.8rem 1rem;margin-bottom:1rem;">{{ session('status') }}</div>@endif
<div class="admin-card" style="padding:1rem 1.2rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.8rem;">
    @foreach ($fields as $field)
        <div>
            <div style="font-size:.75rem;color:var(--admin-text-muted)">{{ $field['label'] }}</div>
            <div>{{ is_scalar($record->{$field['name']}) || $record->{$field['name']} instanceof \Stringable ? $record->{$field['name']} : json_encode($record->{$field['name']}) }}</div>
        </div>
    @endforeach
</div>
@if ($resource === 'issues' && $record->ai_suggestion)
    <div class="admin-card" style="margin-top:1rem;padding:1rem 1.2rem;">
        <h2 class="admin-card-title">Suggested resolution</h2>
        <p style="white-space:pre-wrap;margin-top:.6rem;">{{ $record->ai_suggestion }}</p>
        <p class="page-subtitle">Source: {{ $record->ai_provider }}</p>
    </div>
@endif
<form method="post" action="{{ route('admin.resources.destroy', [$resource, $record->id]) }}" style="margin-top:1rem;" onsubmit="return confirm('Remove this record?')">
    @csrf @method('delete')
    <button class="admin-btn admin-btn-danger" type="submit">Delete</button>
</form>
@endsection
