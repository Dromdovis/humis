#!/usr/bin/env bash
set -e

echo "=================================================="
echo "  Humis — Railway start"
echo "=================================================="
echo "DB_URL is set: $([ -n "$DB_URL" ] && echo 'taip' || echo 'NE !!!')"
echo "APP_URL=${APP_URL}"
echo "PORT=${PORT:-8000}"
echo "--------------------------------------------------"

echo "--> Laukiama MySQL (max 60s)..."
MAX_TRIES=30
TRIES=0
until php -r '
$url = getenv("DB_URL");
if (!$url) { fwrite(STDERR, "DB_URL env var missing\n"); exit(1); }
$p = parse_url($url);
if (!$p || !isset($p["host"])) { fwrite(STDERR, "DB_URL unparseable: $url\n"); exit(1); }
$dsn = sprintf("mysql:host=%s;port=%d;dbname=%s", $p["host"], $p["port"] ?? 3306, ltrim($p["path"] ?? "", "/"));
try {
    new PDO($dsn, $p["user"] ?? null, $p["pass"] ?? null);
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
'; do
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
