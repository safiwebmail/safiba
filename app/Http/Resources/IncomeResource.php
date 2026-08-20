<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
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
            'reference' => $this->reference,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
