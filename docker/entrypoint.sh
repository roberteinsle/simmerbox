#!/bin/sh
set -e

DB_PATH=/var/www/html/database/database.sqlite

# Ensure correct permissions on storage and database directories
echo "[entrypoint] Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/database

# Create SQLite database if it doesn't exist
if [ ! -f "$DB_PATH" ]; then
    echo "[entrypoint] Creating new SQLite database..."
    touch "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
fi

# Backup database before migration (safety net)
if [ -s "$DB_PATH" ]; then
    BACKUP_NAME="$DB_PATH.backup-$(date +%Y%m%d_%H%M%S)"
    echo "[entrypoint] Backing up database to $BACKUP_NAME"
    cp "$DB_PATH" "$BACKUP_NAME"
    # Keep only last 5 backups
    ls -t "$DB_PATH".backup-* 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
fi

# Run migrations (non-destructive, only applies new migrations)
echo "[entrypoint] Running migrations..."
php /var/www/html/artisan migrate --force

# Seed defaults if not already seeded
php /var/www/html/artisan db:seed --class=CategorySeeder --force 2>/dev/null || true
php /var/www/html/artisan db:seed --class=DefaultSettingsSeeder --force 2>/dev/null || true

# Create storage link
php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Cache config and routes
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

echo "[entrypoint] Ready."
exec "$@"
