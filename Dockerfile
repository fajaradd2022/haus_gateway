# ─── Stage 1: Node – build frontend assets ───────────────────────────────────
FROM node:26-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources/ resources/
COPY public/      public/

RUN npm run build


# ─── Stage 2: Composer – install PHP dependencies ────────────────────────────
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

# Install prod deps + dump optimised autoloader in one layer
COPY . .
RUN mkdir -p bootstrap/cache \
 && composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs \
 && composer dump-autoload --optimize --no-dev --no-interaction \
 # package:discover needs a minimal env to bootstrap Laravel
 && cp .env.example .env \
 && php artisan key:generate --ansi \
 && php artisan package:discover --ansi \
 && rm .env


# ─── Stage 3: Production image ───────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS production

LABEL maintainer="Mini Helpdesk AI Assist"

# System dependencies
# linux-headers required for pcntl; libzip-dev for zip; oniguruma-dev for mbstring
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    unzip \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    icu-dev \
    linux-headers

# PHP extensions
RUN docker-php-ext-configure intl \
 && docker-php-ext-install -j"$(nproc)" \
    pdo \
    pdo_mysql \
    mbstring \
    opcache \
    zip \
    bcmath \
    exif \
    pcntl \
    intl

# OPcache – production-tuned settings
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.interned_strings_buffer=16"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# PHP-FPM pool – pass environment variables through, tune workers
RUN { \
    echo "clear_env = no"; \
    echo "pm = dynamic"; \
    echo "pm.max_children = 20"; \
    echo "pm.start_servers = 5"; \
    echo "pm.min_spare_servers = 5"; \
    echo "pm.max_spare_servers = 10"; \
} >> /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# Copy application code from composer stage
COPY --from=composer /app .

# Copy compiled frontend assets from frontend stage
COPY --from=frontend /app/public/build public/build

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Create required directories with correct ownership
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache/data \
             storage/logs \
             bootstrap/cache \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
