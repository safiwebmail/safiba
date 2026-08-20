<?php

namespace App\Support;

use App\Models\Shop;

class ShopScope
{
    public static function resolve($request): array
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $shopId = $request->query('shop_id') ?: $request->input('shop_id');
            if ($shopId && Shop::where('id', $shopId)->exists()) {
                return [(int) $shopId];
            }
            return Shop::where('status', 'active')->pluck('id')->all();
        }

        return $user->shop_id ? [$user->shop_id] : [];
    }
}
