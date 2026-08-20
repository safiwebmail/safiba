<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'photo' => $this->photo ? url('storage/' . $this->photo) : null,
            'phone' => $this->phone,
            'position' => $this->position,
            'salary' => (float) $this->salary,
            'joining_date' => $this->joining_date?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
