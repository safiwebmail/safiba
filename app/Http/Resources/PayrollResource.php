<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => ['id' => $this->employee->id, 'name' => $this->employee->name, 'position' => $this->employee->position]),
            'shop_id' => $this->shop_id,
            'base_salary' => (float) $this->base_salary,
            'bonus' => (float) $this->bonus,
            'deduction' => (float) $this->deduction,
            'net_salary' => (float) $this->net_salary,
            'payment_date' => $this->payment_date->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
