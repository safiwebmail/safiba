<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function store(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cod,pay_at_shop,online'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = $this->orderService->recordPayment(
            $order,
            $validated['amount'],
            $validated['method'],
            $validated['reference'] ?? null
        );

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'payment.create',
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'description' => "Payment of {$validated['amount']} recorded for order {$order->order_number}",
        ]);

        return $this->success([
            'id' => $payment->id,
            'amount' => (float) $payment->amount,
            'method' => $payment->method,
            'payment_status' => $order->fresh()->payment_status,
            'total' => (float) $order->total,
            'paid' => (float) $order->payments()->sum('amount'),
        ], 'Payment recorded');
    }
}
