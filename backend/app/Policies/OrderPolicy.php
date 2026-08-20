<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== 'customer' || true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $order->user_id === $user->id;
        }

        if ($user->isTailor()) {
            return $order->tailor_id === $user->id || $order->user_id === $user->id;
        }

        return $user->canAccessShop($order->shop_id);
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isShopManager();
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTailor()) {
            return $order->tailor_id === $user->id;
        }

        return $user->canAccessShop($order->shop_id);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $this->update($user, $order);
    }
}
