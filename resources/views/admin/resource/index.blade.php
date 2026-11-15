@extends('admin-panel::layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $label }}</h1>
        <p class="page-subtitle">{{ $records->total() }} records</p>
    </div>
    <x-admin-panel::button href="{{ route('admin.resources.create', $resource) }}" icon="plus">New</x-admin-panel::button>
</div>
<form method="get" style="margin:1rem 0;display:flex;gap:.5rem;">
    <input class="admin-input" type="search" name="q" value="{{ request('q') }}" placeholder="Search">
    <button class="admin-btn admin-btn-secondary" type="submit">Search</button>
</form>
<div class="admin-card" style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.92rem;">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th style="text-align:left;padding:.7rem .8rem;color:var(--admin-text-muted);font-weight:600;">{{ str($column)->replace('_',' ')->title() }}</th>
                @endforeach
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr style="border-top:1px solid var(--admin-border);">
                    @foreach ($columns as $column)
                        <td style="padding:.7rem .8rem;">{{ \Illuminate\Support\Str::limit(is_scalar($record->{$column}) || $record->{$column} instanceof \Stringable ? $record->{$column} : json_encode($record->{$column}), 48) }}</td>
                    @endforeach
                    <td style="padding:.7rem .8rem;text-align:right;">
                        <a href="{{ route('admin.resources.show', [$resource, $record->id]) }}">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($columns)+1 }}" style="padding:1.5rem;">Nothing here yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:1rem;">{{ $records->links() }}</div>
@endsection
