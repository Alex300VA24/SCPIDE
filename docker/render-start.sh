#!/usr/bin/env sh
set -eu

php artisan package:discover --ansi
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan view:cache

sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT:-10000}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
