<?php

namespace App\Policies;

use App\Models\User;

class PayrollPolicy
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
