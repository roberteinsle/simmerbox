<?php

namespace App\Policies;

use App\Models\MealPlan;
use App\Models\User;

class MealPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->household_id !== null;
    }

    public function update(User $user, MealPlan $mealPlan): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->household_id && $mealPlan->household_id === $user->household_id;
    }

    public function delete(User $user, MealPlan $mealPlan): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->household_id && $mealPlan->household_id === $user->household_id;
    }
}
