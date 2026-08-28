#!/bin/bash
set -euo pipefail

# storage/ is mounted as a named volume, which hides whatever the image
# baked in at that path — recreate the subdirectories Laravel expects and
# fix ownership before php-fpm (running workers as www-data) starts.
mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

su-exec www-data php artisan storage:link --force
su-exec www-data php artisan package:discover --ansi
su-exec www-data php artisan config:cache
su-exec www-data php artisan route:cache
su-exec www-data php artisan view:cache

exec "$@"
