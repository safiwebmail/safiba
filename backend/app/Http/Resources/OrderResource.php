<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
            'shop' => $this->whenLoaded('shop', fn () => new ShopResource($this->shop)),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'type' => $this->type,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => (float) $this->total,
            'delivery_method' => $this->delivery_method,
            'notes' => $this->notes,
            'tailor_id' => $this->tailor_id,
            'tailor' => $this->whenLoaded('tailor', fn () => new UserResource($this->tailor)),
            'expected_completion_date' => $this->expected_completion_date?->toDateString(),
            'assigned_at' => $this->assigned_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'cancelled_reason' => $this->cancelled_reason,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'total' => (float) $item->total,
                'size' => $item->size,
                'color' => $item->color,
                'garment_type' => $item->garment_type,
                'fabric' => $item->fabric,
                'measurement_profile_id' => $item->measurement_profile_id,
                'measurements' => $item->measurements,
                'design_image' => $item->design_image ? url('storage/' . $item->design_image) : null,
                'instructions' => $item->instructions,
            ])->values()),
            'status_history' => $this->whenLoaded('statusHistory', fn () => $this->statusHistory->map(fn ($h) => [
                'status' => $h->status,
                'note' => $h->note,
                'changed_by' => $h->changedBy?->name,
                'created_at' => $h->created_at?->toISOString(),
            ])->values()),
            'payments' => $this->whenLoaded('payments', fn () => $this->payments->map(fn ($p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'status' => $p->status,
                'reference' => $p->reference,
                'paid_at' => $p->paid_at?->toISOString(),
            ])->values()),
            'delivery' => $this->whenLoaded('delivery', fn () => $this->delivery ? [
                'id' => $this->delivery->id,
                'method' => $this->delivery->method,
                'address' => $this->delivery->address,
                'fee' => (float) $this->delivery->fee,
                'status' => $this->delivery->status,
                'staff_id' => $this->delivery->staff_id,
                'delivery_date' => $this->delivery->delivery_date?->toDateString(),
                'notes' => $this->delivery->notes,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
