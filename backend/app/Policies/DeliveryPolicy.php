<?php

namespace App\Policies;

use App\Models\User;

class DeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isShopManager();
    }

    public function create(User $user): bool
    {
        return $user->isShopManager();
    }

    public function update(User $user): bool
    {
        return $user->isShopManager();
    }
}
