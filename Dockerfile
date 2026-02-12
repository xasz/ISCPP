# Base image
FROM php:8.4-fpm

# -----------------------------
# 1️⃣ System dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
        unzip \
        git \
        curl \
        libpq-dev \
        libsqlite3-dev \
        npm \
        jq \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# 2️⃣ Composer
# -----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------
# 3️⃣ Arbeitsverzeichnis
# -----------------------------
WORKDIR /var/www

# -----------------------------
# 5️⃣ Entrypoint
# -----------------------------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# -----------------------------
# 6️⃣ Expose
# -----------------------------
EXPOSE 9000

# -----------------------------
# 7️⃣ Entrypoint & Default CMD
# -----------------------------
ENTRYPOINT ["sh", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
