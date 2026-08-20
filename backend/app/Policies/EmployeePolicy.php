<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isShopManager();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->canAccessShop($employee->shop_id) && $user->isShopManager();
    }

    public function create(User $user): bool
    {
        return $user->isShopManager();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->canAccessShop($employee->shop_id) && $user->isShopManager();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isAdmin();
    }
}
