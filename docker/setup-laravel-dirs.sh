#!/bin/bash
set -e

echo "Setting up Laravel directories and permissions..."

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel directory setup completed successfully"
