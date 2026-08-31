#!/bin/sh
set -e

DATA_DIR=/app/data
KEY_FILE="$DATA_DIR/app.key"

mkdir -p "$DATA_DIR" "$DATA_DIR/visuels" "$DATA_DIR/cartes"
mkdir -p /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/storage/logs

if [ -z "$APP_KEY" ]; then
    if [ ! -s "$KEY_FILE" ]; then
        php -r 'echo "base64:" . base64_encode(random_bytes(32));' > "$KEY_FILE"
    fi
    export APP_KEY="$(cat "$KEY_FILE")"
fi

[ -f "$DATA_DIR/database.sqlite" ] || touch "$DATA_DIR/database.sqlite"

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec "$@"
