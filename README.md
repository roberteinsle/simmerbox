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

## Deploy on Coolify

Simmerbox runs as a single Docker container on [Coolify](https://coolify.io/) (self-hosted PaaS).

### 1. Create Resource

1. In Coolify, go to your project and click **+ Add Resource**
2. Select **Docker** > **Dockerfile**
3. Connect your Git repository: `https://github.com/roberteinsle/simmerbox`
4. Branch: `main`
5. Coolify will auto-detect the `Dockerfile`

### 2. Configure Volumes (CRITICAL)

Under **Storages**, add two persistent volumes:

| Container Path | Purpose |
|---|---|
| `/var/www/html/database` | SQLite database - **all your data lives here** |
| `/var/www/html/storage/app` | Uploaded images and media |

> **WARNING:** Without persistent volumes, ALL DATA IS LOST on every redeploy! The database is automatically backed up before each migration (last 5 backups kept).

### 3. Set Environment Variables

Under **Environment Variables**, configure:

```
APP_NAME=Simmerbox
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=http://your-server-ip:port
APP_TIMEZONE=Europe/Berlin
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
BROADCAST_CONNECTION=log
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_LEVEL=warning
```

Generate `APP_KEY` locally: `php artisan key:generate --show`
Or: `echo "base64:$(openssl rand -base64 32)"`

### 4. Network / Ports

- Set the exposed port to **80**
- Coolify's built-in proxy (Traefik) handles HTTPS once you assign a domain

### 5. Deploy

Click **Deploy**. The build takes ~2 minutes. On first start, the container will:
1. Create the SQLite database
2. Run all migrations
3. Seed default categories and settings

After deployment, register the first user and make them admin via Coolify's terminal:

```bash
php /var/www/html/artisan tinker --execute="App\Models\User::first()->update(['is_admin' => true]);"
```

### Enable WebSocket (optional)

To enable realtime sync (grocery list, meal plan), set `BROADCAST_CONNECTION=reverb` and enable the Reverb supervisor program in `docker/supervisord.conf` (`autostart=true`). Expose port `8080` additionally.

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
