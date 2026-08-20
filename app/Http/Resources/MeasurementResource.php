<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'height' => $this->height !== null ? (float) $this->height : null,
            'chest' => $this->chest !== null ? (float) $this->chest : null,
            'waist' => $this->waist !== null ? (float) $this->waist : null,
            'hip' => $this->hip !== null ? (float) $this->hip : null,
            'shoulder' => $this->shoulder !== null ? (float) $this->shoulder : null,
            'sleeve' => $this->sleeve !== null ? (float) $this->sleeve : null,
            'neck' => $this->neck !== null ? (float) $this->neck : null,
            'shirt_length' => $this->shirt_length !== null ? (float) $this->shirt_length : null,
            'trouser_length' => $this->trouser_length !== null ? (float) $this->trouser_length : null,
            'wrist' => $this->wrist !== null ? (float) $this->wrist : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
