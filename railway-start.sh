#!/usr/bin/env bash
set -e

echo "--> Laukiama duomenų bazės..."
until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "    DB dar neprieinama, laukiu 2s..."
    sleep 2
done
echo "--> DB prieinama"

echo "--> Vykdomos migracijos"
php artisan migrate --force

echo "--> Kešuojama konfigūracija"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--> Paleidžiamas serveris ant porto ${PORT:-8000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
