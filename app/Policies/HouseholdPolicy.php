<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

class HouseholdPolicy
{
    public function view(User $user, Household $household): bool
    {
        return $user->household_id === $household->id || $user->is_admin;
    }

    public function update(User $user, Household $household): bool
    {
        return $household->owner_id === $user->id || $user->is_admin;
    }

    public function delete(User $user, Household $household): bool
    {
        return $household->owner_id === $user->id || $user->is_admin;
    }

    public function manageInvites(User $user, Household $household): bool
    {
        return $household->owner_id === $user->id || $user->is_admin;
    }
}
