@extends('admin-panel::layouts.app')

@section('content')
@php($kpi = app(\App\Modules\Dashboard\Services\KpiService::class)->snapshot())
<div class="space-y-6">
    <div class="page-header">
        <div>
            <h1 class="page-title">Plant floor</h1>
            <p class="page-subtitle">Open work, yield, and stock that needs a buyer — this week on the floor.</p>
        </div>
        <div class="page-actions">
            <x-admin-panel::button href="{{ route('admin.estimate') }}" variant="secondary" icon="sparkles">Estimate</x-admin-panel::button>
            <x-admin-panel::button href="{{ route('admin.shop-floor.index') }}" icon="package">Shop floor</x-admin-panel::button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">
        @foreach ([
            ['Open orders', $kpi['open_orders']],
            ['Live jobs', $kpi['jobs_live']],
            ['Overdue', $kpi['overdue']],
            ['OEE', $kpi['oee'].'%'],
            ['Waste', $kpi['waste_percent'].'%'],
            ['Rejects', $kpi['reject_rate'].'%'],
            ['Revenue MTD', number_format($kpi['revenue'], 0)],
            ['Defects 30d', $kpi['defects']],
        ] as [$label, $value])
            <x-admin-panel::card>
                <div style="font-size:.8rem;color:var(--admin-text-muted)">{{ $label }}</div>
                <div style="font-size:1.6rem;font-weight:650;margin-top:.35rem">{{ $value }}</div>
            </x-admin-panel::card>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;">
        <x-admin-panel::card>
            <h2 class="admin-card-title">Output vs plan</h2>
            <div style="display:flex;align-items:flex-end;gap:.6rem;height:160px;margin-top:1rem;">
                @foreach ($kpi['series'] as $day)
                    @php($max = max(1, $kpi['series']->max('plan'), $kpi['series']->max('good')))
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.25rem;height:100%;justify-content:flex-end;">
                        <div style="width:100%;display:flex;gap:2px;align-items:flex-end;height:130px;">
                            <div style="flex:1;background:var(--admin-primary);opacity:.35;height:{{ ($day['plan'] / $max) * 100 }}%"></div>
                            <div style="flex:1;background:var(--admin-primary);height:{{ ($day['good'] / $max) * 100 }}%"></div>
                        </div>
                        <span style="font-size:.7rem;color:var(--admin-text-muted)">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-admin-panel::card>
        <x-admin-panel::card>
            <h2 class="admin-card-title">Reorder</h2>
            @forelse ($kpi['alerts'] as $alert)
                <div style="display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--admin-border);font-size:.9rem;">
                    <span>{{ $alert['name'] }}</span>
                    <span>{{ $alert['available'] }} / {{ $alert['reorder'] }}</span>
                </div>
            @empty
                <p class="page-subtitle">No material is under its reorder level.</p>
            @endforelse
        </x-admin-panel::card>
    </div>
</div>
@endsection
