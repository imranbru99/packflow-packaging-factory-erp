@extends('admin-panel::layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $record->exists ? 'Edit' : 'New' }} {{ $label }}</h1>
    </div>
</div>
<form method="post" action="{{ $record->exists ? route('admin.resources.update', [$resource, $record->id]) : route('admin.resources.store', $resource) }}" class="admin-card" style="padding:1.25rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
    @csrf
    @if ($record->exists) @method('put') @endif
    @foreach ($fields as $field)
        <label style="display:flex;flex-direction:column;gap:.35rem;font-size:.85rem;">
            <span>{{ $field['label'] }}</span>
            @if ($field['type'] === 'textarea')
                <textarea class="admin-input" name="{{ $field['name'] }}" rows="4">{{ old($field['name'], $record->{$field['name']}) }}</textarea>
            @elseif ($field['type'] === 'select')
                <select class="admin-input" name="{{ $field['name'] }}">
                    <option value="">—</option>
                    @foreach ($field['options'] ?? [] as $id => $name)
                        <option value="{{ $id }}" @selected(old($field['name'], $record->{$field['name']}) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            @elseif ($field['type'] === 'checkbox')
                <input type="checkbox" name="{{ $field['name'] }}" value="1" @checked(old($field['name'], $record->{$field['name']}))>
            @else
                <input class="admin-input" type="{{ $field['type'] === 'password' ? 'password' : ($field['type'] === 'datetime-local' ? 'datetime-local' : $field['type']) }}" name="{{ $field['name'] }}" value="{{ $field['type'] === 'password' ? '' : old($field['name'], $record->{$field['name']} instanceof \DateTimeInterface ? $record->{$field['name']}->format($field['type']==='date' ? 'Y-m-d' : 'Y-m-d\TH:i') : $record->{$field['name']}) }}">
            @endif
        </label>
    @endforeach
    <div style="grid-column:1/-1;">
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
@if ($errors->any())
    <div class="admin-card" style="margin-top:1rem;padding:1rem;color:#b42318;">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
@endsection
