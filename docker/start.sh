#!/usr/bin/env sh
set -e

cd /var/www/html

rm -f public/hot

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

php artisan optimize:clear || true
php artisan package:discover --ansi || true

if [ "${APP_ENV}" = "production" ]; then
    echo "Running database migrations..."
    migration_success=0
    for i in $(seq 1 20); do
        if php artisan migrate --force --no-interaction; then
            migration_success=1
            break
        fi
        echo "Migration failed (attempt ${i}/20), retrying in 3s..."
        sleep 3
    done

    if [ "${migration_success}" -ne 1 ]; then
        echo "Migrations did not complete successfully."
        exit 1
    fi
else
    php artisan migrate --force --no-interaction || true
fi

php artisan optimize || true

php-fpm -D
exec nginx -g "daemon off;"
