<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        if ($product->shop_id === null) {
            return true;
        }
        return $user->canAccessShop($product->shop_id);
    }

    public function create(User $user): bool
    {
        return $user->isShopManager();
    }

    public function update(User $user, Product $product): bool
    {
        if ($product->shop_id !== null && !$user->canAccessShop($product->shop_id)) {
            return false;
        }
        return $user->isShopManager();
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
