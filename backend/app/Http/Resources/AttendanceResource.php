<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => ['id' => $this->employee->id, 'name' => $this->employee->name, 'position' => $this->employee->position]),
            'shop_id' => $this->shop_id,
            'date' => $this->date->toDateString(),
            'status' => $this->status,
            'check_in' => $this->check_in?->format('H:i'),
            'check_out' => $this->check_out?->format('H:i'),
            'notes' => $this->notes,
        ];
    }
}
