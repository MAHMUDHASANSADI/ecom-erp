<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
        .header { background: #2d3748; color: white; padding: 20px 24px; }
        .content { padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #f7fafc; text-align: left; padding: 8px 12px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; border-top: 2px solid #2d3748; }
        .footer { background: #f7fafc; padding: 16px 24px; color: #718096; font-size: 13px; }
    </style>
</head>
<body>
<div class="header">
    <h2 style="margin:0;">Order Confirmation</h2>
    <p style="margin:4px 0 0;">Thank you for your order, {{ $order->customer_name }}!</p>
</div>

<div class="content">
    <p>Your order has been received and is being processed. Here are the details:</p>

    <table>
        <tr><td style="width:140px;color:#718096;">Order #</td><td><strong>{{ $order->id }}</strong></td></tr>
        <tr><td style="color:#718096;">Date</td><td>{{ $order->created_at->format('d M Y H:i') }}</td></tr>
        <tr><td style="color:#718096;">Status</td><td>{{ ucfirst($order->status) }}</td></tr>
        <tr><td style="color:#718096;">Delivery Address</td><td>{{ nl2br(e($order->address)) }}</td></tr>
        <tr><td style="color:#718096;">Payment</td><td>Cash on Delivery</td></tr>
    </table>

    <h4 style="margin-top:24px;">Order Items</h4>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Unit Price</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name ?? '—' }}</td>
                <td style="text-align:center;">{{ $item->quantity }}</td>
                <td style="text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:right;">${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align:right;">Order Total</td>
                <td style="text-align:right;">${{ number_format($order->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:24px;">
        We will contact you to arrange delivery. If you have any questions, please reply to this email.
    </p>
</div>

<div class="footer">
    <p>{{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}</p>
</div>
</body>
</html>
