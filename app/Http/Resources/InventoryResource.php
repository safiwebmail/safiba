<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'category' => $this->category,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'cost' => (float) $this->cost,
            'selling_price' => (float) $this->selling_price,
            'min_stock' => (float) $this->min_stock,
            'status' => $this->status,
            'is_low_stock' => $this->is_low_stock,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
