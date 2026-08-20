<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_name' => $this->manager_name,
            'manager_id' => $this->manager_id,
            'opening_hours' => $this->opening_hours,
            'status' => $this->status,
            'manager' => $this->whenLoaded('manager', fn () => new UserResource($this->manager)),
        ];
    }
}
