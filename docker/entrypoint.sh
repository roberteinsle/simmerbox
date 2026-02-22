#!/bin/sh

LOG=/var/www/html/storage/logs/entrypoint.log

log() {
    echo "[entrypoint] $1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG" 2>/dev/null || true
}

DB_PATH=/var/www/html/database/database.sqlite

# Ensure directories exist
mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

log "Starting Simmerbox initialization..."
log "DB_PATH=$DB_PATH"
log "DB_DATABASE env=${DB_DATABASE:-<not set>}"
log "APP_ENV=${APP_ENV:-<not set>}"
log "APP_KEY set: $([ -n "$APP_KEY" ] && echo yes || echo NO)"

# Set permissions
log "Setting permissions..."
chmod -R 777 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache 2>/dev/null || true

# Create SQLite database if it doesn't exist
if [ ! -f "$DB_PATH" ]; then
    log "Creating new SQLite database..."
    touch "$DB_PATH"
    chmod 666 "$DB_PATH"
    chown www-data:www-data "$DB_PATH" 2>/dev/null || true
fi

# Backup database
if [ -s "$DB_PATH" ]; then
    BACKUP_NAME="$DB_PATH.backup-$(date +%Y%m%d_%H%M%S)"
    log "Backing up database to $BACKUP_NAME"
    cp "$DB_PATH" "$BACKUP_NAME" || true
    ls -t "$DB_PATH".backup-* 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
fi

# Clear stale config cache
log "Clearing config cache..."
rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/routes*.php
rm -f /var/www/html/bootstrap/cache/services.php

# Check database state
log "Checking database state..."
HAS_USERS=$(sqlite3 "$DB_PATH" "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='users';" 2>/dev/null || echo "0")
HAS_SESSIONS=$(sqlite3 "$DB_PATH" "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='sessions';" 2>/dev/null || echo "0")
ALL_TABLES=$(sqlite3 "$DB_PATH" "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;" 2>/dev/null || echo "<sqlite3 error>")
log "users=$HAS_USERS sessions=$HAS_SESSIONS tables=[$ALL_TABLES]"

# Drop stale partial state
if [ "$HAS_USERS" = "0" ] && [ "$HAS_SESSIONS" = "1" ]; then
    log "Broken partial state detected – dropping stale tables..."
    sqlite3 "$DB_PATH" "
        DROP TABLE IF EXISTS sessions;
        DROP TABLE IF EXISTS cache;
        DROP TABLE IF EXISTS cache_locks;
        DROP TABLE IF EXISTS jobs;
        DROP TABLE IF EXISTS migrations;
    " 2>&1 | tee -a "$LOG"
fi

# Run migrations – force DB_DATABASE to the known path
log "Running artisan migrate..."
MIGRATE_OUT=$(DB_DATABASE="$DB_PATH" php /var/www/html/artisan migrate --force 2>&1)
MIGRATE_EXIT=$?
log "migrate exit=$MIGRATE_EXIT output: $MIGRATE_OUT"

if [ $MIGRATE_EXIT -ne 0 ]; then
    log "ERROR: artisan migrate failed! Attempting php -r sanity check..."
    DB_DATABASE="$DB_PATH" php -r "
        define('LARAVEL_START', microtime(true));
        require '/var/www/html/vendor/autoload.php';
        echo 'PHP OK' . PHP_EOL;
    " 2>&1 | tee -a "$LOG"
fi

# Verify tables after migration
TABLES_AFTER=$(sqlite3 "$DB_PATH" "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;" 2>/dev/null || echo "<error>")
log "Tables after migrate: [$TABLES_AFTER]"

# Seed defaults
log "Seeding defaults..."
DB_DATABASE="$DB_PATH" php /var/www/html/artisan db:seed --class=CategorySeeder --force 2>&1 | tee -a "$LOG" || true
DB_DATABASE="$DB_PATH" php /var/www/html/artisan db:seed --class=DefaultSettingsSeeder --force 2>&1 | tee -a "$LOG" || true

# Build search index
log "Building search index..."
DB_DATABASE="$DB_PATH" php /var/www/html/artisan search:reindex 2>/dev/null || true

# Storage link
DB_DATABASE="$DB_PATH" php /var/www/html/artisan storage:link --force 2>/dev/null || true

# Cache config and routes
log "Caching config and routes..."
DB_DATABASE="$DB_PATH" php /var/www/html/artisan config:cache 2>&1 | tee -a "$LOG"
DB_DATABASE="$DB_PATH" php /var/www/html/artisan route:cache 2>&1 | tee -a "$LOG"
DB_DATABASE="$DB_PATH" php /var/www/html/artisan view:cache 2>/dev/null || true

# Fix permissions after cache commands
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

log "Ready. Starting supervisor..."
exec "$@"
