#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Add it to your .env file (php artisan key:generate --show)." >&2
    exit 1
fi

# The storage volume may be freshly created, so make sure Laravel's folders exist.
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public

php artisan migrate --force
php artisan storage:link --force
php artisan config:cache
php artisan view:cache

# The commands above run as root; hand everything they wrote back to Apache's user.
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
