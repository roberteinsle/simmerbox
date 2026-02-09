#!/bin/sh
set -e

# Create SQLite database if it doesn't exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Run migrations
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

exec "$@"
