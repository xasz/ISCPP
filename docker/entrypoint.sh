#!/bin/sh
set -e

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
# 1️⃣ DB Ready? (optional)
# -----------------------------
if [ -n "$DB_HOST" ] && [ -n "$DB_PORT" ]; then
  echo "Waiting for DB $DB_HOST:$DB_PORT..."
  while ! nc -z "$DB_HOST" "$DB_PORT"; do
    sleep 1
  done
fi

# -----------------------------
# 2️⃣ Laravel setup
# -----------------------------
if [ -f artisan ]; then
    echo "Running migrations and caching..."
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi


# -----------------------------
# 3️⃣ Exec original command
# -----------------------------
exec "$@"
