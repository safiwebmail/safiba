<?php

namespace App\Policies;

use App\Models\MeasurementProfile;
use App\Models\User;

class MeasurementProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MeasurementProfile $profile): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isTailor()) {
            return true;
        }
        return $profile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isAdmin();
    }

    public function update(User $user, MeasurementProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    public function delete(User $user, MeasurementProfile $profile): bool
    {
        return $profile->user_id === $user->id || $user->isAdmin();
    }
}
