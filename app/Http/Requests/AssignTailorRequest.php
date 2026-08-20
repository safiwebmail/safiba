<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTailorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isShopManager();
    }

    public function rules(): array
    {
        return [
            'tailor_id' => ['required', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ];
    }
}
