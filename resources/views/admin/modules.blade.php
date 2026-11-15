@extends('admin-panel::layouts.app')
@section('content')
<div class="page-header"><div><h1 class="page-title">Modules</h1><p class="page-subtitle">Switch a domain off for this installation. Sales, the floor, and the website stop offering it.</p></div></div>
@if (session('status'))<p>{{ session('status') }}</p>@endif
<form method="post" action="{{ route('admin.modules.update') }}" class="admin-card" style="padding:1rem;">
    @csrf
    @foreach ($modules as $key => $module)
        <label style="display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--admin-border);">
            <span>{{ $module['label'] }}</span>
            <input type="checkbox" name="modules[{{ $key }}]" value="1" @checked($module['enabled'])>
        </label>
    @endforeach
    <button class="admin-btn admin-btn-primary" style="margin-top:1rem;" type="submit">Save switches</button>
</form>
@endsection
