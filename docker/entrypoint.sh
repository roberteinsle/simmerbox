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

# Clear any stale cached config before migrating (prevents wrong DB path from old cache)
echo "[entrypoint] Clearing config cache..."
php /var/www/html/artisan config:clear 2>/dev/null || true
php /var/www/html/artisan cache:clear 2>/dev/null || true

# Run migrations
echo "[entrypoint] Running migrations..."
php /var/www/html/artisan migrate --force || { echo "[entrypoint] ERROR: migrations failed!"; exit 1; }

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
