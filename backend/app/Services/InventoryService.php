<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function adjust(InventoryItem $item, string $type, float $quantity, ?string $reason = null, ?string $reference = null, ?int $userId = null): InventoryItem
    {
        return DB::transaction(function () use ($item, $type, $quantity, $reason, $reference, $userId) {
            if ($type === 'out' && $item->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Available: {$item->quantity} {$item->unit}",
                ]);
            }

            $balance = $type === 'in'
                ? $item->quantity + $quantity
                : $item->quantity - $quantity;

            $item->quantity = $balance;
            $item->save();

            StockMovement::create([
                'inventory_id' => $item->id,
                'shop_id' => $item->shop_id,
                'type' => $type,
                'quantity' => $quantity,
                'balance' => $balance,
                'reason' => $reason,
                'reference' => $reference,
                'user_id' => $userId ?? auth()->id(),
            ]);

            return $item;
        });
    }
}
