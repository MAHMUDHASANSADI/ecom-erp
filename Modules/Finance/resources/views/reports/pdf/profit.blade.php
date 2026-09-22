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
        .positive { color: #276749; font-weight: bold; }
        .negative { color: #c53030; font-weight: bold; }
        .section-header td { background: #edf2f7; font-weight: bold; font-size: 11px; letter-spacing: .04em; }
        .total-row td { font-weight: bold; border-top: 2px solid #2d3748; background: #f7fafc; font-size: 13px; }
        .footer { margin-top: 24px; font-size: 10px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
<h2>{{ $appName }} — Profit Report</h2>
<p class="sub">
    {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
    &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
</p>

<table>
    <tbody>
        <tr class="section-header"><td colspan="2">INCOME</td></tr>
        <tr>
            <td>Revenue (completed sales)</td>
            <td class="text-right positive">{{ $currencySymbol }}{{ number_format($revenue, 2) }}</td>
        </tr>

        <tr class="section-header"><td colspan="2">COST OF GOODS SOLD</td></tr>
        <tr>
            <td>Cost of goods sold (cost price × qty)</td>
            <td class="text-right negative">− {{ $currencySymbol }}{{ number_format($cogs, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Gross Profit</strong> <span style="color:#666;">({{ $grossMarginPct }}% margin)</span></td>
            <td class="text-right {{ $grossProfit >= 0 ? 'positive' : 'negative' }}">
                {{ $currencySymbol }}{{ number_format($grossProfit, 2) }}
            </td>
        </tr>

        <tr class="section-header"><td colspan="2">OPERATING EXPENSES</td></tr>
        @foreach($expenseByCategory as $cat => $amt)
        <tr>
            <td style="padding-left:20px;">{{ $cat }}</td>
            <td class="text-right negative">− {{ $currencySymbol }}{{ number_format($amt, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td><strong>Total Expenses</strong></td>
            <td class="text-right negative">− {{ $currencySymbol }}{{ number_format($totalExpenses, 2) }}</td>
        </tr>

        <tr class="total-row">
            <td>NET PROFIT</td>
            <td class="text-right {{ $netProfit >= 0 ? 'positive' : 'negative' }}">
                {{ $currencySymbol }}{{ number_format($netProfit, 2) }}
            </td>
        </tr>
    </tbody>
</table>

<div class="footer">{{ $appName }} — Confidential</div>
</body>
</html>
