#!/bin/sh
set -e

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
