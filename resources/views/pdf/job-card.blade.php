<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Job card {{ $job->number }}</title>
    <style>body{font-family:"Noto Sans Bengali",sans-serif;font-size:12px} table{width:100%;border-collapse:collapse} td,th{border-bottom:1px solid #ccc;padding:4px;text-align:left}</style>
</head>
<body>
    <h1>{{ $company->name }}</h1>
    <h2>Job card {{ $job->number }}</h2>
    <p>{{ $job->product->name ?? $job->notes }} · Qty {{ $job->quantity }} · Customer {{ $job->salesOrder->customer->name ?? '—' }}</p>
    <h3>Routing</h3>
    <table>
        @foreach ($job->stages as $stage)
            <tr><td>{{ $stage->sequence }}</td><td>{{ $stage->name }}</td><td>{{ $stage->workCenter->name ?? '' }}</td><td>{{ $stage->status }}</td></tr>
        @endforeach
    </table>
    <h3>Material</h3>
    <table>
        @foreach ($job->requirements as $need)
            <tr><td>{{ $need->material->sku }}</td><td>{{ $need->material->name }}</td><td>{{ $need->required_qty }} {{ $need->uom }}</td><td>Reserved {{ $need->reserved_qty }}</td></tr>
        @endforeach
    </table>
</body>
</html>
