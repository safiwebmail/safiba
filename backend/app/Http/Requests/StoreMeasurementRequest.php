<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'height' => ['nullable', 'numeric'],
            'chest' => ['nullable', 'numeric'],
            'waist' => ['nullable', 'numeric'],
            'hip' => ['nullable', 'numeric'],
            'shoulder' => ['nullable', 'numeric'],
            'sleeve' => ['nullable', 'numeric'],
            'neck' => ['nullable', 'numeric'],
            'shirt_length' => ['nullable', 'numeric'],
            'trouser_length' => ['nullable', 'numeric'],
            'wrist' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
