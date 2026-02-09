<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Recipe $recipe): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $this->checkPermission($user, $recipe, 'permissions.recipe_view');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Recipe $recipe): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $this->checkPermission($user, $recipe, 'permissions.recipe_edit');
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $this->checkPermission($user, $recipe, 'permissions.recipe_delete');
    }

    protected function checkPermission(User $user, Recipe $recipe, string $settingKey): bool
    {
        $level = settings($settingKey, 'household');

        return match ($level) {
            'everyone' => true,
            'owner' => $recipe->user_id === $user->id,
            // 'household' (default): Ersteller ODER gleiches Haushalt
            default => $recipe->user_id === $user->id
                || ($user->household_id && $recipe->household_id === $user->household_id),
        };
    }
}
