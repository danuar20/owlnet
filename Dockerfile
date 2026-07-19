# syntax=docker/dockerfile:1

# PHP 8.4-FPM runtime for Hermes ISP Billing.
FROM php:8.4-fpm

# System dependencies required by Laravel and common PHP extensions.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        iputils-ping \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libpq-dev \
        unzip \
        zip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions used by Laravel + PostgreSQL + Redis.
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip opcache

# PECL extensions (Redis / igbinary) installed via the bundled pecl client.
RUN pecl install redis igbinary \
    && docker-php-ext-enable redis opcache

# Composer (global) for local tweaks inside the container if needed.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Application working directory.
WORKDIR /var/www

# The container runs as the www-data user; the source is mounted at runtime.
COPY . .

# Install PHP dependencies (composer.json + lock are part of the build context,
# but we keep vendor out of the image and mount it at runtime for DX).
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || true

# OpCache tuning for production.
RUN { \
        echo "opcache.memory_consumption=128"; \
        echo "opcache.interned_strings_buffer=8"; \
        echo "opcache.max_accelerated_files=4000"; \
        echo "opcache.revalidate_freq=2"; \
        echo "opcache.fast_shutdown=1"; \
    } > /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000

CMD ["php-fpm"]
