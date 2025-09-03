#!/bin/bash

# Nettoyage des caches Laravel
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache de config
php artisan config:cache
php artisan route:cache

# Migration forcée
php artisan migrate --force

# Lancer Apache
exec apache2-foreground