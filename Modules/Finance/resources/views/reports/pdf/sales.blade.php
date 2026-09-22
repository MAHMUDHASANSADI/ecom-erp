<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h2 { margin: 0 0 4px; font-size: 16px; }
        .sub { color: #666; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #2d3748; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        tfoot td { font-weight: bold; background: #f7fafc; border-top: 2px solid #2d3748; }
        .footer { margin-top: 24px; font-size: 10px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
<h2>{{ $appName }} — Sales Report</h2>
<p class="sub">
    {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
    &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
</p>

<table>
    <thead>
        <tr>
            <th>Sale #</th>
            <th>Date</th>
            <th>Channel</th>
            <th>Payment</th>
            <th>Cashier</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
        <tr>
            <td>{{ $sale->sale_number }}</td>
            <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
            <td>{{ $sale->channel }}</td>
            <td>{{ $sale->payment_method }}</td>
            <td>{{ $sale->cashier?->name ?? '—' }}</td>
            <td class="text-right">{{ $currencySymbol }}{{ number_format($sale->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="text-right">Total Revenue ({{ $sales->count() }} transactions)</td>
            <td class="text-right">{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">{{ $appName }} — Confidential</div>
</body>
</html>
