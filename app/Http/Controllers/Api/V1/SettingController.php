<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function business(Request $request)
    {
        $business = Business::first();

        if (!$business) {
            $business = Business::create([
                'name' => 'Safi Tailoring',
                'currency' => 'AFN',
                'timezone' => 'Asia/Kabul',
                'order_prefix' => 'ORD',
                'invoice_prefix' => 'INV',
            ]);
        }

        return $this->success([
            'name' => $business->name,
            'logo' => $business->logo ? url('storage/' . $business->logo) : null,
            'phone' => $business->phone,
            'email' => $business->email,
            'whatsapp' => $business->whatsapp,
            'address' => $business->address,
            'currency' => $business->currency,
            'timezone' => $business->timezone,
            'default_delivery_fee' => (float) $business->default_delivery_fee,
            'order_prefix' => $business->order_prefix,
            'invoice_prefix' => $business->invoice_prefix,
        ], 'Success');
    }

    public function updateBusiness(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'default_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'order_prefix' => ['nullable', 'string', 'max:10'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $business = Business::firstOrCreate();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('branding', 'public');
        }

        $business->update($validated);

        return $this->success(null, 'Settings updated');
    }

    public function publicSettings()
    {
        $business = Business::first();
        $defaultFee = $business?->default_delivery_fee ?? 0;
        $whatsapp = $business?->whatsapp ?? null;

        $shopSettings = Setting::where('key', 'delivery_fee')->get()->pluck('value', 'shop_id');

        return $this->success([
            'business' => $business ? [
                'name' => $business->name,
                'logo' => $business->logo ? url('storage/' . $business->logo) : null,
                'phone' => $business->phone,
                'email' => $business->email,
                'whatsapp' => $business->whatsapp,
                'address' => $business->address,
                'currency' => $business->currency,
            ] : null,
            'default_delivery_fee' => (float) $defaultFee,
            'delivery_fees' => $shopSettings,
        ], 'Success');
    }
}
