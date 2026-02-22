#!/bin/sh

echo "[entrypoint] Starting Simmerbox initialization..."

DB_PATH=/var/www/html/database/database.sqlite

# Ensure directories exist (safe even as non-root)
mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

# Set permissions – chmod 777 as fallback so any user (www-data, root, etc.) can write
echo "[entrypoint] Setting permissions..."
chmod -R 777 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache 2>/dev/null || true

# Create SQLite database if it doesn't exist
if [ ! -f "$DB_PATH" ]; then
    echo "[entrypoint] Creating new SQLite database..."
    touch "$DB_PATH"
    chmod 666 "$DB_PATH"
    chown www-data:www-data "$DB_PATH" 2>/dev/null || true
fi

# Backup database before migration (safety net)
if [ -s "$DB_PATH" ]; then
    BACKUP_NAME="$DB_PATH.backup-$(date +%Y%m%d_%H%M%S)"
    echo "[entrypoint] Backing up database to $BACKUP_NAME"
    cp "$DB_PATH" "$BACKUP_NAME" || true
    # Keep only last 5 backups
    ls -t "$DB_PATH".backup-* 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
fi

# Clear any stale cached config before migrating
echo "[entrypoint] Clearing config cache..."
rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/routes*.php
rm -f /var/www/html/bootstrap/cache/services.php

# Check if database is in a broken/partial state (has stale tables but no users table)
echo "[entrypoint] Checking database state..."
HAS_USERS=$(sqlite3 "$DB_PATH" "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='users';" 2>/dev/null || echo "0")
HAS_SESSIONS=$(sqlite3 "$DB_PATH" "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='sessions';" 2>/dev/null || echo "0")

echo "[entrypoint] users table exists: $HAS_USERS, sessions table exists: $HAS_SESSIONS"

if [ "$HAS_USERS" = "0" ] && [ "$HAS_SESSIONS" = "1" ]; then
    echo "[entrypoint] Detected broken partial state – dropping stale tables and starting fresh..."
    sqlite3 "$DB_PATH" "
        DROP TABLE IF EXISTS sessions;
        DROP TABLE IF EXISTS cache;
        DROP TABLE IF EXISTS cache_locks;
        DROP TABLE IF EXISTS jobs;
        DROP TABLE IF EXISTS migrations;
    " 2>&1
fi

# Run migrations
echo "[entrypoint] Running migrations..."
if [ "$HAS_USERS" = "0" ]; then
    echo "[entrypoint] Fresh database – running migrate (all migrations)..."
    php /var/www/html/artisan migrate --force 2>&1 || echo "[entrypoint] ERROR: migrate failed!"
else
    echo "[entrypoint] Existing database – running incremental migrate..."
    php /var/www/html/artisan migrate --force 2>&1 || echo "[entrypoint] ERROR: migrate failed!"
fi

# Seed defaults if not already seeded
echo "[entrypoint] Seeding defaults..."
php /var/www/html/artisan db:seed --class=CategorySeeder --force 2>/dev/null || true
php /var/www/html/artisan db:seed --class=DefaultSettingsSeeder --force 2>/dev/null || true

# Build search index
echo "[entrypoint] Building search index..."
php /var/www/html/artisan search:reindex 2>/dev/null || true

# Create storage link
php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Cache config and routes
echo "[entrypoint] Caching config and routes..."
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Fix permissions again after artisan cache commands (they may create root-owned files)
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "[entrypoint] Ready. Starting supervisor..."
exec "$@"
