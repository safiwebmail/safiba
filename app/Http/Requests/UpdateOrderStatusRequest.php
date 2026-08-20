<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,confirmed,assigned,cutting,stitching,quality_check,ready,delivered,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
