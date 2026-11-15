@extends('admin-panel::layouts.app')
@section('content')
<div class="page-header"><div><h1 class="page-title">Ready to run</h1><p class="page-subtitle">Jobs that are planned, released, or on a machine.</p></div></div>
@if ($groups)
    <div class="admin-card" style="padding:1rem;margin-bottom:1rem;">
        <h2 class="admin-card-title">Group these to cut changeovers</h2>
        @foreach ($groups as $key => $numbers)
            <p>{{ $key }}: {{ implode(', ', $numbers) }}</p>
        @endforeach
    </div>
@endif
@foreach ($jobs as $job)
    <a href="{{ route('admin.shop-floor.show', $job) }}" class="admin-card" style="display:block;padding:1rem;margin-bottom:.6rem;">
        <strong>{{ $job->number }}</strong> · {{ $job->product->name ?? $job->notes }}
        <span style="float:right">{{ $job->status }}</span>
    </a>
@endforeach
{{ $jobs->links() }}
@endsection
