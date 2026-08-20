<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(array $data, ?int $userId = null, bool $isCustom = false): Order
    {
        return DB::transaction(function () use ($data, $userId, $isCustom) {
            $orderNumber = generate_order_number();

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'shop_id' => $data['shop_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'] ?? null,
                'type' => $isCustom ? 'custom' : 'product',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? 'cod',
                'subtotal' => $data['subtotal'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'delivery_fee' => $data['delivery_fee'] ?? 0,
                'total' => $data['total'] ?? 0,
                'delivery_method' => $data['delivery_method'] ?? 'pickup',
                'notes' => $data['notes'] ?? null,
                'expected_completion_date' => $data['expected_completion_date'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['price'] ?? 0,
                    'total' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'garment_type' => $item['garment_type'] ?? null,
                    'fabric' => $item['fabric'] ?? null,
                    'measurement_profile_id' => $item['measurement_profile_id'] ?? null,
                    'measurements' => $item['measurements'] ?? null,
                    'design_image' => $item['design_image'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                ]);
            }

            $this->recordStatus($order, 'pending', 'Order placed');

            if ($order->delivery_method === 'delivery') {
                $order->delivery()->create([
                    'shop_id' => $order->shop_id,
                    'method' => 'delivery',
                    'address' => $order->customer_address,
                    'fee' => $order->delivery_fee,
                    'status' => 'pending',
                ]);
            }

            return $order;
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?string $note = null, ?int $userId = null): Order
    {
        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['assigned', 'cancelled'],
            'assigned' => ['cutting', 'cancelled'],
            'cutting' => ['stitching', 'cancelled'],
            'stitching' => ['quality_check', 'cancelled'],
            'quality_check' => ['ready', 'cancelled'],
            'ready' => ['delivered', 'completed'],
            'delivered' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowed[$order->status] ?? [])) {
            throw ValidationException::withMessages([
                'status' => "Cannot change order from {$order->status} to {$newStatus}.",
            ]);
        }

        $order->status = $newStatus;
        $order->assigned_at = $newStatus === 'assigned' ? now() : $order->assigned_at;
        $order->completed_at = in_array($newStatus, ['delivered', 'completed']) ? now() : $order->completed_at;
        $order->cancelled_at = $newStatus === 'cancelled' ? now() : $order->cancelled_at;
        $order->save();

        $this->recordStatus($order, $newStatus, $note, $userId);

        return $order;
    }

    public function assignTailor(Order $order, int $tailorId, ?string $note = null): Order
    {
        $order->tailor_id = $tailorId;
        $order->assigned_at = now();
        $order->status = 'assigned';
        $order->save();

        $this->recordStatus($order, 'assigned', $note ?? 'Order assigned to tailor');

        return $order;
    }

    public function recordStatus(Order $order, string $status, ?string $note = null, ?int $userId = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'note' => $note,
            'changed_by' => $userId ?? auth()->id(),
        ]);
    }

    public function recordPayment(Order $order, float $amount, string $method, ?string $reference = null): Payment
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'shop_id' => $order->shop_id,
            'amount' => $amount,
            'method' => $method,
            'status' => 'completed',
            'reference' => $reference,
            'paid_at' => now(),
            'added_by' => auth()->id(),
        ]);

        $paid = $order->payments()->sum('amount');
        $order->payment_status = $paid >= $order->total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
        $order->save();

        return $payment;
    }
}
