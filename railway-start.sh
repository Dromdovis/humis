#!/usr/bin/env bash
set -e

echo "=================================================="
echo "  Humis — Railway start"
echo "=================================================="
echo "DB_HOST=${DB_HOST}"
echo "DB_PORT=${DB_PORT}"
echo "DB_DATABASE=${DB_DATABASE}"
echo "DB_USERNAME=${DB_USERNAME}"
echo "APP_URL=${APP_URL}"
echo "PORT=${PORT:-8000}"
echo "--------------------------------------------------"

echo "--> Laukiama MySQL (max 60s)..."
MAX_TRIES=30
TRIES=0
until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { fwrite(STDERR, \$e->getMessage() . PHP_EOL); exit(1); }"; do
    TRIES=$((TRIES+1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "!! DB neprieinama po ${MAX_TRIES} bandymų, nutraukiama"
        exit 1
    fi
    echo "    DB dar neprieinama (bandymas ${TRIES}/${MAX_TRIES}), laukiu 2s..."
    sleep 2
done
echo "--> DB prieinama"

echo "--> Vykdomos migracijos"
php artisan migrate --force

echo "--> Kešuojama konfigūracija"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--> Paleidžiamas serveris ant 0.0.0.0:${PORT:-8000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
