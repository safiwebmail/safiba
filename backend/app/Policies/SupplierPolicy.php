<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isShopManager();
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->canAccessShop($supplier->shop_id) && $user->isShopManager();
    }

    public function create(User $user): bool
    {
        return $user->isShopManager();
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->canAccessShop($supplier->shop_id) && $user->isShopManager();
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->isAdmin();
    }
}
