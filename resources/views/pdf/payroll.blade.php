<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Slip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 20px; }
        .shop-name { font-size: 22px; font-weight: bold; }
        .muted { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f3f3f3; }
        .right { text-align: right; }
        .grand { font-size: 16px; font-weight: bold; }
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
            <div style="font-size:18px; font-weight:bold;">PAY SLIP</div>
            <div class="muted">#{{ $payroll->id }}</div>
            <div class="muted">{{ $payroll->payment_date->format('d M Y') }}</div>
        </div>
    </div>

    <h3>{{ $payroll->employee->name }}</h3>
    <div class="muted">{{ $payroll->employee->position }} — {{ $payroll->employee->shop->name ?? '' }}</div>

    <table>
        <tbody>
            <tr><td>Base Salary</td><td class="right">{{ number_format($payroll->base_salary, 2) }}</td></tr>
            <tr><td>Bonus</td><td class="right">{{ number_format($payroll->bonus, 2) }}</td></tr>
            <tr><td>Deduction</td><td class="right">- {{ number_format($payroll->deduction, 2) }}</td></tr>
            <tr><td class="grand">Net Salary</td><td class="right grand">{{ number_format($payroll->net_salary, 2) }} {{ $business->currency }}</td></tr>
        </tbody>
    </table>

    @if ($payroll->notes)
    <div style="margin-top:16px"><strong>Notes:</strong> {{ $payroll->notes }}</div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
