@extends('admin-panel::layouts.app')
@section('content')
<div class="page-header"><div><h1 class="page-title">Estimate a pack</h1><p class="page-subtitle">Blank size, board or film weight, machine time, and a selling price. Paste a customer spec or type the numbers.</p></div></div>
<form method="post" action="{{ route('admin.estimate.store') }}" enctype="multipart/form-data" class="admin-card" style="padding:1rem;display:grid;gap:.7rem;">
    @csrf
    <textarea class="admin-input" name="raw_text" rows="3" placeholder="400 x 300 x 200 mm, B flute, 140 GSM, 3 colours, 5000 pcs">{{ old('raw_text') }}</textarea>
    <input class="admin-input" type="file" name="document">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.5rem;">
        <select class="admin-input" name="product_type"><option>corrugated</option><option>flexible</option><option>label</option><option>carton</option></select>
        <input class="admin-input" name="description" placeholder="Description" value="RSC shipper">
        <input class="admin-input" name="quantity" type="number" value="1000">
        <input class="admin-input" name="length_mm" type="number" step="0.1" placeholder="L mm" value="400">
        <input class="admin-input" name="width_mm" type="number" step="0.1" placeholder="W mm" value="300">
        <input class="admin-input" name="height_mm" type="number" step="0.1" placeholder="H mm" value="200">
        <input class="admin-input" name="gsm" type="number" step="0.1" placeholder="GSM" value="140">
        <input class="admin-input" name="flute" placeholder="Flute" value="C">
        <input class="admin-input" name="ink_colors" type="number" value="2">
        <input class="admin-input" name="margin_percent" type="number" step="0.1" value="18">
    </div>
    <label><input type="checkbox" name="save" value="1"> Save as quotation</label>
    <button class="admin-btn admin-btn-primary" type="submit">Calculate</button>
</form>
@if ($estimate)
    <div class="admin-card" style="margin-top:1rem;padding:1rem;">
        <h2 class="admin-card-title">{{ $estimate['style'] }}</h2>
        <p>Blank {{ $estimate['blank_length_mm'] }} × {{ $estimate['blank_width_mm'] }} mm · {{ $estimate['material_kg'] }} kg · {{ number_format($estimate['machine_minutes'], 1) }} min</p>
        <p>Material {{ number_format($estimate['material_cost'], 2) }} · machine {{ number_format($estimate['machine_cost'], 2) }} · labour {{ number_format($estimate['labor_cost'], 2) }}</p>
        <p><strong>Unit {{ number_format($estimate['unit_price'], 4) }} · total {{ number_format($estimate['line_total'], 2) }} BDT</strong></p>
        @foreach ($estimate['flags'] as $flag)<p>{{ $flag }}</p>@endforeach
    </div>
@endif
@endsection
