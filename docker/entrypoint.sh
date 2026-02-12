#!/bin/sh
set -e

APP_DIR=/var/www
cd $APP_DIR

# -----------------------------
# Function to set / overwrite env
# -----------------------------
set_env() {
    KEY=$1
    VALUE=$2
    if grep -q "^$KEY=" .env; then
        sed -i "s|^$KEY=.*|$KEY=$VALUE|" .env
    else
        echo "$KEY=$VALUE" >> .env
    fi
}

# -----------------------------
# Optional: Custom CA Import
# -----------------------------
if [ "$IMPORT_CUSTOM_CA" = "1" ] && [ -f /tmp/custom-ca.crt ]; then
    echo "Importing custom CA..."
    cp /tmp/custom-ca.crt /usr/local/share/ca-certificates/custom-ca.crt
    update-ca-certificates
    echo "openssl.cafile=/etc/ssl/certs/ca-certificates.crt" > /usr/local/etc/php/conf.d/99-ca.ini
    echo "curl.cainfo=/etc/ssl/certs/ca-certificates.crt" >> /usr/local/etc/php/conf.d/99-ca.ini
fi

# -----------------------------
# DB Ready? (optional)
# -----------------------------
if [ -n "$DB_HOST" ] && [ -n "$DB_PORT" ]; then
  echo "Waiting for DB $DB_HOST:$DB_PORT..."
  while ! nc -z "$DB_HOST" "$DB_PORT"; do
    sleep 1
  done
fi

# -----------------------------
# .env prüfen / erstellen
# -----------------------------

ENV_FILE="/var/www/env-volume/.env"
LINK_FILE="/var/www/.env"

if [ ! -f "$ENV_FILE" ]; then
    echo "Creating .env file in volume..."
    touch "$ENV_FILE"
    echo "APP_KEY=" >> "$ENV_FILE"
fi

if [ ! -L "$LINK_FILE" ]; then
    if [ -e "$LINK_FILE" ]; then
        echo "$LINK_FILE already exists and is not a symlink, skipping symlink creation."
    else
        echo "Creating symlink for .env file in project directory..."
        ln -s "$ENV_FILE" "$LINK_FILE"
    fi
fi
# -----------------------------
# APP_KEY generieren falls leer
# -----------------------------
if ! grep -q '^APP_KEY=.' .env; then
    echo "Generating new APP_KEY..."
    php artisan key:generate --force
fi

# -----------------------------
# ENV Variablen in .env synchronisieren
# -----------------------------
[ -n "$APP_ENV" ] && set_env "APP_ENV" "$APP_ENV"
[ -n "$APP_DEBUG" ] && set_env "APP_DEBUG" "$APP_DEBUG"
[ -n "$APP_URL" ] && set_env "APP_URL" "$APP_URL"
[ -n "$DB_CONNECTION" ] && set_env "DB_CONNECTION" "$DB_CONNECTION"
[ -n "$DB_HOST" ] && set_env "DB_HOST" "$DB_HOST"
[ -n "$DB_PORT" ] && set_env "DB_PORT" "$DB_PORT"
[ -n "$DB_DATABASE" ] && set_env "DB_DATABASE" "$DB_DATABASE"
[ -n "$DB_USERNAME" ] && set_env "DB_USERNAME" "$DB_USERNAME"
[ -n "$DB_PASSWORD" ] && set_env "DB_PASSWORD" "$DB_PASSWORD"
[ -n "$USER_DEFAULT_TIMEZONE" ] && set_env "USER_DEFAULT_TIMEZONE" "$USER_DEFAULT_TIMEZONE"

# -----------------------------
# Laravel Migration & Cache
# -----------------------------
if [ -f artisan ]; then
    echo "Running migrations and caching..."
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# -----------------------------
# Start original CMD
# -----------------------------
exec "$@"
