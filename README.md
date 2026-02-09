# Simmerbox

Self-hosted recipe management web app. Store recipes, plan weekly meals, generate grocery lists, and share everything within your household.

## Features

- **Recipes** - Full CRUD with image upload, categories, tags, ingredients, and preparation steps
- **JSON Import** - Import recipes via structured JSON
- **Meal Planning** - Assign one recipe per day, weekly navigation, print view
- **Grocery Lists** - Auto-generated from meal plan, manual entries, checkbox UI
- **Recurring Groceries** - NLP-based input in German (e.g. "woechentlich am Montag", "alle 2 Wochen")
- **Full-Text Search** - SQLite FTS5 with BM25 ranking and category/tag filters
- **Household System** - Share data via invite code or email invitation
- **Realtime Sync** - Live updates via WebSockets (Laravel Reverb)
- **Admin Panel** - Settings, user management, categories
- **Permissions** - Configurable (everyone / household / owner)
- **Dark Mode** - Toggle with browser persistence
- **Print Views** - Print-CSS for meal plan and grocery lists
- **German UI** - Entire interface in German

## Tech Stack

- **Backend:** Laravel 11 + PHP 8.2+
- **Database:** SQLite
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Auth:** Laravel Breeze
- **Realtime:** Laravel Reverb (WebSockets)
- **Images:** Intervention Image v3 (GD driver)
- **Deployment:** Docker / Coolify

## Local Setup

```bash
git clone https://github.com/roberteinsle/simmerbox.git
cd simmerbox

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

php artisan storage:link
php artisan search:reindex

php artisan serve
```

The app is available at `http://localhost:8000`.

To make the first registered user an admin:

```bash
php artisan tinker
> App\Models\User::first()->update(['is_admin' => true]);
```

## Docker Setup

```bash
git clone https://github.com/roberteinsle/simmerbox.git
cd simmerbox

# Generate app key (one-time)
echo "APP_KEY=base64:$(openssl rand -base64 32)" > .env

docker compose up -d --build
```

The app runs at `http://localhost:8000`.

### Environment Variables

| Variable | Default | Description |
|---|---|---|
| `APP_KEY` | - | Laravel app key (required) |
| `APP_URL` | `http://localhost:8000` | Public URL |
| `APP_PORT` | `8000` | HTTP port |
| `REVERB_PORT` | `8080` | WebSocket port |
| `REVERB_HOST` | `localhost` | WebSocket host |

### Volumes

- `db-data` - SQLite database (persistent data)
- `storage-data` - Uploads (recipe images)

## Configuration

Most settings can be configured via the **Admin Panel** (gear icon > Admin area):

- **General** - App name, description
- **Authentication** - Open registration or invite-only
- **Permissions** - Who can view/edit/delete recipes
- **Meal Plan** - Default portions, week start day
- **Upload** - Max image size
- **Email** - Sender configuration

## Recurring Groceries

The system understands German recurrence patterns:

| Input | Result |
|---|---|
| `Milch, 1L, taeglich` | Every day |
| `Brot, 1, woechentlich am Montag` | Every Monday |
| `Kaese, 200g, alle 2 Wochen am Freitag` | Every 2 weeks on Friday |
| `Waschmittel, 1, monatlich am 1.` | 1st of every month |

## Scheduled Commands

For recurring groceries, the Laravel scheduler must be running:

```bash
# Crontab (local)
* * * * * cd /path/to/simmerbox && php artisan schedule:run >> /dev/null 2>&1
```

In the Docker container, the scheduler runs automatically via Supervisor.

## License

MIT
