@extends('admin-panel::layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $job->number }}</h1>
        <p class="page-subtitle">{{ $job->product->name ?? $job->notes }} · {{ $job->quantity }} pcs · {{ $job->status }}</p>
    </div>
</div>
@if (session('status'))<div class="admin-card" style="padding:.8rem 1rem;margin-bottom:1rem;">{{ session('status') }}</div>@endif
<div style="display:grid;gap:.8rem;">
    @foreach ($job->stages as $stage)
        <div class="admin-card" style="padding:1rem;">
            <strong>{{ $stage->sequence }}. {{ $stage->name }}</strong>
            <span style="float:right">{{ $stage->status }}</span>
            <p class="page-subtitle">{{ $stage->machine->name ?? 'Unassigned machine' }} · good {{ $stage->good_qty }} · waste {{ $stage->waste_qty }}</p>
            @if (in_array($stage->status, ['ready', 'running', 'pending']))
                <form method="post" action="{{ route('admin.shop-floor.complete', $stage) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem;">
                    @csrf
                    <input class="admin-input" style="max-width:140px" type="number" step="0.001" name="good_qty" placeholder="Good qty" required value="{{ $stage->planned_qty }}">
                    <input class="admin-input" style="max-width:140px" type="number" step="0.001" name="waste_qty" placeholder="Waste" value="0">
                    <input class="admin-input" style="max-width:140px" type="number" step="0.1" name="downtime_minutes" placeholder="Downtime min" value="0">
                    <input class="admin-input" style="max-width:220px" name="downtime_reason" placeholder="Downtime reason">
                    <button class="admin-btn admin-btn-primary" type="submit">Complete stage</button>
                </form>
            @endif
        </div>
    @endforeach
</div>
@endsection
