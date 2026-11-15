<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $company->name }} — {{ $invoice->number }}</title>
    <style>
        body { font-family: "Noto Sans Bengali", sans-serif; font-size: 12px; color: #1c1917; }
        h1 { font-size: 20px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #d6d3d1; text-align: left; padding: 6px 4px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $company->name }}</h1>
    <p>{{ $company->name_bn }} · {{ $company->address }} {{ $company->city }}</p>
    <p>চালান / Invoice {{ $invoice->number }} · {{ $invoice->issued_on?->toDateString() }}</p>
    <p>Bill to {{ $invoice->customer->name }}</p>
    <table>
        <thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Rate</th><th class="right">Amount</th></tr></thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="right">{{ $line->quantity }}</td>
                    <td class="right">{{ number_format($line->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="right">Subtotal {{ number_format($invoice->subtotal, 2) }} {{ $company->currency }}</p>
    <p class="right">{{ $company->tax_name }} {{ $invoice->tax_percent }}% {{ number_format($invoice->tax_amount, 2) }}</p>
    <p class="right"><strong>Total {{ number_format($invoice->total, 2) }} {{ $company->currency }}</strong></p>
</body>
</html>
