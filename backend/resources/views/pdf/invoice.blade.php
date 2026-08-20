<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 20px; }
        .shop-name { font-size: 22px; font-weight: bold; }
        .muted { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f3f3f3; }
        .right { text-align: right; }
        .totals { margin-top: 12px; text-align: right; }
        .totals div { margin: 3px 0; }
        .grand { font-size: 16px; font-weight: bold; margin-top: 6px; }
        .footer { margin-top: 30px; text-align: center; color: #888; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 10px; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.unpaid { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="shop-name">{{ $business->name }}</div>
            <div class="muted">{{ $business->address }}</div>
            <div class="muted">{{ $business->phone }} | {{ $business->email }}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:18px; font-weight:bold;">INVOICE</div>
            <div class="muted">#{{ $order->order_number }}</div>
            <div class="muted">{{ $order->created_at->format('d M Y') }}</div>
            <div class="muted">Shop: {{ $order->shop->name }}</div>
        </div>
    </div>

    <div style="display:flex; justify-content:space-between;">
        <div>
            <strong>Customer</strong>
            <div>{{ $order->customer_name }}</div>
            <div class="muted">{{ $order->customer_phone }}</div>
            <div class="muted">{{ $order->customer_address }}</div>
        </div>
        <div style="text-align:right">
            <strong>Payment Status</strong>
            <div><span class="badge {{ $order->payment_status }}">{{ strtoupper($order->payment_status) }}</span></div>
            <div class="muted">Method: {{ $order->payment_method }}</div>
            <div class="muted">Order status: {{ $order->status }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->name }} @if($item->size) ({{ $item->size }}) @endif</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td class="right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>Subtotal: <strong>{{ number_format($order->subtotal, 2) }}</strong></div>
        <div>Discount: {{ number_format($order->discount, 2) }}</div>
        <div>Delivery: {{ number_format($order->delivery_fee, 2) }}</div>
        <div class="grand">Total: {{ number_format($order->total, 2) }} {{ $business->currency }}</div>
    </div>

    <div class="footer">
        Thank you for shopping with {{ $business->name }} | Generated on {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
