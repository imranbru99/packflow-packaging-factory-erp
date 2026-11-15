@extends('admin-panel::layouts.app')
@section('content')
<div class="page-header"><div><h1 class="page-title">Attendance</h1><p class="page-subtitle">Punches land in laravel-attendance-hub. Operators on the floor are the same people on the job card.</p></div></div>
@foreach ($devices as $device)
    <p>{{ $device->name }} · {{ $device->provider }} · {{ $device->status }}</p>
@endforeach
<div class="admin-card" style="padding:1rem;margin-top:1rem;">
    @foreach ($logs as $log)
        <p>{{ $log->punched_at }} · employee {{ $log->employee_id }} · {{ $log->punch_type }}</p>
    @endforeach
</div>
@endsection
