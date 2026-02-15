# Simmerbox - Claude Code Guidelines

## Project Overview
Self-hosted recipe management web app built with Laravel 11. German UI. Stores recipes, meal plans, grocery lists, and supports household sharing with realtime sync.

## Tech Stack
- **Backend:** Laravel 11, PHP 8.2+, SQLite
- **Frontend:** Blade templates, Tailwind CSS, Alpine.js
- **Auth:** Laravel Breeze (Blade stack)
- **Realtime:** Laravel Reverb (WebSockets)
- **Images:** Intervention Image v3 (GD driver)
- **Deployment:** Docker (single container with Supervisor)

## Project Structure
```
app/
  Console/Commands/       # ReindexSearch, UpdateRecurringGroceries
  Enums/                  # FrequencyType, PermissionLevel, RegistrationMode
  Events/                 # GroceryItemToggled, GroceryListUpdated, MealPlanUpdated
  Http/Controllers/       # Standard Laravel controllers
    Admin/                # SettingsController, UserController, CategoryController
  Http/Middleware/         # EnsureAdmin, EnsureHasHousehold
  Http/Requests/          # StoreRecipeRequest, UpdateRecipeRequest
  Models/                 # Eloquent models
  Observers/              # RecipeObserver, IngredientObserver (FTS sync)
  Policies/               # RecipePolicy, MealPlanPolicy, GroceryListPolicy, HouseholdPolicy
  Services/               # Business logic services
  View/Components/        # AppLayout, GuestLayout
resources/views/
  recipes/                # index, show, create, edit, import, _form partial
  meal-plan/              # index, print
  groceries/              # index, print
  recurring-groceries/    # index
  household/              # show
  admin/                  # settings, users, categories
  search/                 # index
  layouts/                # app, guest, navigation
  components/             # Blade components
  errors/                 # 403, 404, 419, 500
```

## Key Architecture Decisions

### Recipe IDs
Recipes use **millisecond Unix timestamps** as IDs (`$incrementing = false`, generated in `Recipe::booted()`). Not auto-incrementing.

### Database
SQLite with FTS5 virtual table for full-text search. FTS synced via model observers.

### Settings System
Custom key-value settings (`settings` table) with `SettingsService` singleton and global `settings('key', default)` helper. Settings use dot-notation keys (e.g., `auth.registration_mode`). Admin form fields use array notation `name="settings[key.with.dots]"` to preserve dots.

### Permissions
Three-level system: `everyone`, `household`, `owner`. Configurable via admin settings. Policies check `PermissionLevel` enum.

### Color Theme
Custom `olive` color palette in Tailwind (primary: `#61902c`). All UI uses `olive-*` classes.

### Realtime
Private channel per household (`household.{id}`). Events broadcast with `->toOthers()`.

### Image Upload
Drag-and-drop upload with Alpine.js. `ImageService` uses lazy GD initialization (`??=`) to avoid crashes when GD extension is missing.

## Common Commands

```bash
# Development
php artisan serve                    # Start dev server
npm run dev                          # Vite dev server
npm run build                        # Build frontend assets

# Database
php artisan migrate --seed           # Run migrations + seeders
php artisan migrate:fresh --seed     # Reset DB completely
php artisan search:reindex           # Rebuild FTS5 index

# Admin
php artisan tinker                   # Interactive shell
# > App\Models\User::where('email', '...')->update(['is_admin' => true])

# Docker
docker compose up -d --build         # Build and start
docker compose down                  # Stop
```

## Important Notes

- **Laravel 11 base Controller is empty** - `AuthorizesRequests` trait is explicitly added in `app/Http/Controllers/Controller.php`
- **UI language is German** - All labels, messages, validation in German
- **Codespace proxy** - `trustProxies(at: '*')` configured in `bootstrap/app.php` for GitHub Codespace compatibility
- **Admin settings form** uses array field names (`settings[key]`) to avoid underscore-to-dot conversion issues
- **GD extension** required for image processing - `ImageService` uses lazy init as fallback
- **FTS5 sync** happens via `RecipeObserver` and `IngredientObserver` - always run `search:reindex` after bulk data changes

## Versioning

This project uses a timestamp-hash versioning scheme for every commit.

### Format

```
1.0.0-<hash>
```

Where `<hash>` is the first 6 characters of the SHA-256 hash of the current timestamp (`YYYYMMDDHHmm`).

### Generation

Before every commit, generate the version string:

```bash
HASH=$(echo -n "$(date +%Y%m%d%H%M)" | sha256sum | cut -c1-6)
VERSION="1.0.0-${HASH}"
```

### Rules

- Always generate a fresh version string before committing.
- Store the version in the project's designated version file (e.g. `VERSION`, `package.json`, or equivalent).
- Use the generated version string as part of the commit message: `release: 1.0.0-<hash>`.
- The prefix `1.0.0` stays fixed unless manually updated by the developer.
- Never reuse or hardcode a previous hash.
