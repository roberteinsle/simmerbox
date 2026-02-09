<?php

namespace App\Providers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Observers\IngredientObserver;
use App\Observers\RecipeObserver;
use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Recipe::observe(RecipeObserver::class);
        Ingredient::observe(IngredientObserver::class);

        // Config-Override aus Settings (nur wenn DB verfuegbar)
        try {
            $settings = app(SettingsService::class);
            $appName = $settings->get('general.app_name');
            if ($appName) {
                config(['app.name' => $appName]);
            }
        } catch (\Exception $e) {
            // DB noch nicht verfuegbar (z.B. waehrend Migration)
        }
    }
}
