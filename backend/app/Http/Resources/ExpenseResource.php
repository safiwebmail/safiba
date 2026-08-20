<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'category' => $this->category,
            'amount' => (float) $this->amount,
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'receipt' => $this->receipt_path ? url('storage/' . $this->receipt_path) : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
