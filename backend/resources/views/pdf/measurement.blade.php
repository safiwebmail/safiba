<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Measurement Sheet</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 20px; }
        .shop-name { font-size: 22px; font-weight: bold; }
        .muted { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f3f3f3; }
        .measure { display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 6px 0; }
        .footer { margin-top: 30px; text-align: center; color: #888; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="shop-name">{{ $business->name }}</div>
            <div class="muted">{{ $business->phone }} | {{ $business->address }}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:18px; font-weight:bold;">MEASUREMENT SHEET</div>
            <div class="muted">Order: #{{ $order->order_number }}</div>
            <div class="muted">Shop: {{ $order->shop->name }}</div>
        </div>
    </div>

    <div><strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->customer_phone }})</div>

    @foreach ($order->items as $item)
    <h3>{{ $item->name }} @if($item->garment_type) — {{ $item->garment_type }} @endif @if($item->fabric) ({{ $item->fabric }}) @endif</h3>
    @if ($item->measurements && count($item->measurements))
        <table>
            <thead>
                <tr><th>Measurement</th><th class="right">Value (inches)</th></tr>
            </thead>
            <tbody>
                @foreach ($item->measurements as $key => $value)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td class="right">{{ $value }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="muted">No inline measurements provided.</div>
    @endif
    @if ($item->instructions)
    <div><strong>Instructions:</strong> {{ $item->instructions }}</div>
    @endif
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
