#!/bin/bash

# Run Laravel setup
php artisan config:clear
php artisan route:clear
php artisan migrate --force

# Start php-fpm in background
php-fpm -D

# Start nginx in foreground (keeps container alive)
nginx -g "daemon off;"