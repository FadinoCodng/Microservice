# Image PHP yang sesuai dengan dependency Laravel
FROM php:8.4-cli

# Instal kebutuhan MySQL dan ekstensi PHP
RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client unzip git \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Instal Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY . .

# Instal dependency Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 3003

# Jalankan migrasi dan server Laravel
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=3003"]
