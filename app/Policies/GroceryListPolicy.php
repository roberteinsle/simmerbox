<?php

namespace App\Policies;

use App\Models\GroceryList;
use App\Models\User;

class GroceryListPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GroceryList $groceryList): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->household_id && $groceryList->household_id === $user->household_id;
    }

    public function create(User $user): bool
    {
        return $user->household_id !== null;
    }

    public function modify(User $user, GroceryList $groceryList): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->household_id && $groceryList->household_id === $user->household_id;
    }

    public function delete(User $user, GroceryList $groceryList): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->household_id && $groceryList->household_id === $user->household_id;
    }
}
