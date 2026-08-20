<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isCustomer() || $this->user()->isShopManager();
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:cod,pay_at_shop'],
            'delivery_method' => ['required', 'in:pickup,delivery'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'expected_completion_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.size' => ['nullable', 'string'],
            'items.*.color' => ['nullable', 'string'],
            'items.*.garment_type' => ['nullable', 'string'],
            'items.*.fabric' => ['nullable', 'string'],
            'items.*.measurement_profile_id' => ['nullable', 'exists:measurement_profiles,id'],
            'items.*.measurements' => ['nullable', 'array'],
            'items.*.design_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'items.*.instructions' => ['nullable', 'string'],
        ];
    }
}
