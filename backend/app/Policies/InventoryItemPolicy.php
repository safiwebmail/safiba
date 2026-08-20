<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isShopManager();
    }

    public function view(User $user, InventoryItem $item): bool
    {
        return $user->canAccessShop($item->shop_id) && $user->isShopManager();
    }

    public function create(User $user): bool
    {
        return $user->isShopManager();
    }

    public function update(User $user, InventoryItem $item): bool
    {
        return $user->canAccessShop($item->shop_id) && $user->isShopManager();
    }

    public function delete(User $user, InventoryItem $item): bool
    {
        return $user->isAdmin();
    }
}
