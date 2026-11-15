@extends('admin-panel::layouts.auth')
@section('content')
<h2 style="margin-top:0;">Factory login</h2>
<p class="page-subtitle">Demo plant: admin@packflow.test / password</p>
@if ($errors->any())<p style="color:#b42318;">{{ $errors->first() }}</p>@endif
<form method="post" action="{{ route('login') }}" style="display:grid;gap:.7rem;">
    @csrf
    <input class="admin-input" type="email" name="email" value="{{ old('email', 'admin@packflow.test') }}" required>
    <input class="admin-input" type="password" name="password" value="password" required>
    <label><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
    <button class="admin-btn admin-btn-primary" type="submit">Enter the floor</button>
</form>
<p class="page-subtitle" style="margin-top:1rem;">Also: sales@, supervisor@, qc@, warehouse@, finance@, operator@, customer@ — all @packflow.test</p>
@endsection
