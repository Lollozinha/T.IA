#!/bin/sh
set -e
cd /var/www/html

export COMPOSER_HOME=/tmp/composer
export COMPOSER_ALLOW_SUPERUSER=1

mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

# independente de quem for o dono no host (1000, 1001, root…)
chmod -R a+rwX storage bootstrap/cache

if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist --no-ansi
fi

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ -f .env ] && ! grep -qE '^APP_KEY=base64:' .env; then
  php artisan key:generate --force --no-interaction
fi

php artisan migrate --force --no-interaction

exec docker-php-entrypoint php-fpm
