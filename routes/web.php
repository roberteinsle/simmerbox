<?php

use App\Http\Controllers\GroceryListController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\RecurringGroceryController;
use App\Http\Controllers\HouseholdInvitationController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeExportImportController;
use App\Http\Controllers\RecipeRatingController;
use App\Http\Controllers\RecipeImportController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureHasHousehold;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard redirects to recipes
    Route::get('/dashboard', function () {
        return redirect()->route('recipes.index');
    })->name('dashboard');

    // Search
    Route::get('/search', SearchController::class)->name('search');

    // Recipes
    Route::resource('recipes', RecipeController::class);
    Route::get('/recipes/{recipe}/print', [RecipeController::class, 'print'])->name('recipes.print');
    Route::post('/recipes/{recipe}/rate', [RecipeRatingController::class, 'store'])->name('recipes.rate');
    Route::delete('/recipes/{recipe}/rate', [RecipeRatingController::class, 'destroy'])->name('recipes.rate.destroy');

    // Recipe Import/Export
    Route::get('/recipes-import', [RecipeImportController::class, 'create'])->name('recipes.import');
    Route::post('/recipes-import', [RecipeImportController::class, 'store'])->name('recipes.import.store');
    Route::get('/recipes-export', [RecipeExportImportController::class, 'export'])->name('recipes.export');
    Route::post('/recipes-import-zip', [RecipeExportImportController::class, 'import'])->name('recipes.import-zip');

    // Household
    Route::get('/household', [HouseholdController::class, 'show'])->name('household.show');
    Route::post('/household', [HouseholdController::class, 'store'])->name('household.store');
    Route::put('/household', [HouseholdController::class, 'update'])->name('household.update');
    Route::delete('/household', [HouseholdController::class, 'destroy'])->name('household.destroy');
    Route::delete('/household/leave', [HouseholdController::class, 'leave'])->name('household.leave');
    Route::post('/household/regenerate-code', [HouseholdController::class, 'regenerateInviteCode'])->name('household.regenerate-code');

    // Household Invitations
    Route::post('/household/invite', [HouseholdInvitationController::class, 'store'])->name('household.invite');
    Route::get('/household/join/{token}', [HouseholdInvitationController::class, 'accept'])->name('household.join');
    Route::post('/household/join-code', [HouseholdInvitationController::class, 'joinViaCode'])->name('household.join-code');

    // Meal Plan (requires household)
    Route::middleware(EnsureHasHousehold::class)->group(function () {
        Route::get('/meal-plan', [MealPlanController::class, 'index'])->name('meal-plan.index');
        Route::post('/meal-plan', [MealPlanController::class, 'store'])->name('meal-plan.store');
        Route::delete('/meal-plan/{mealPlan}', [MealPlanController::class, 'destroy'])->name('meal-plan.destroy');
        Route::patch('/meal-plan/{mealPlan}/move', [MealPlanController::class, 'move'])->name('meal-plan.move');
        Route::get('/meal-plan/print', [MealPlanController::class, 'print'])->name('meal-plan.print');

        // Groceries
        Route::post('/recipes/{recipe}/add-to-groceries', [GroceryListController::class, 'addFromRecipe'])->name('recipes.add-to-groceries');
        Route::get('/groceries', [GroceryListController::class, 'index'])->name('groceries.index');
        Route::post('/groceries/generate', [GroceryListController::class, 'generate'])->name('groceries.generate');
        Route::post('/groceries/add-item', [GroceryListController::class, 'addItem'])->name('groceries.add-item');
        Route::patch('/groceries/toggle/{groceryItem}', [GroceryListController::class, 'toggleItem'])->name('groceries.toggle-item');
        Route::delete('/groceries/remove/{groceryItem}', [GroceryListController::class, 'removeItem'])->name('groceries.remove-item');
        Route::delete('/groceries/{groceryList}', [GroceryListController::class, 'destroy'])->name('groceries.destroy');
        Route::get('/groceries/{groceryList}/print', [GroceryListController::class, 'print'])->name('groceries.print');

        // Recurring Groceries
        Route::get('/recurring-groceries', [RecurringGroceryController::class, 'index'])->name('recurring-groceries.index');
        Route::post('/recurring-groceries', [RecurringGroceryController::class, 'store'])->name('recurring-groceries.store');
        Route::patch('/recurring-groceries/{recurringGrocery}/toggle', [RecurringGroceryController::class, 'toggleActive'])->name('recurring-groceries.toggle');
        Route::delete('/recurring-groceries/{recurringGrocery}', [RecurringGroceryController::class, 'destroy'])->name('recurring-groceries.destroy');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin
    Route::middleware(EnsureAdmin::class)->prefix('admin')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
        Route::patch('/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories');
        Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    });
});

require __DIR__.'/auth.php';
