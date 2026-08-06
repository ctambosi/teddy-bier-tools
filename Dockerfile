# syntax=docker/dockerfile:1
FROM php:8.4-fpm AS base

RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# ============================================================================
FROM base AS vendor

COPY composer.json composer.lock ./
RUN curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install \
        --no-dev \
        --no-interaction \
        --no-autoloader \
        --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize

# ============================================================================
FROM node:20-alpine AS assets

WORKDIR /var/www
COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ============================================================================
FROM base AS dev

RUN curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /usr/local/etc/php/conf.d && \
    echo "opcache.enable=0" > /usr/local/etc/php/conf.d/opcache.ini

COPY docker/php/entrypoint.dev.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]

# ============================================================================
FROM base AS prod

RUN mkdir -p /usr/local/etc/php/conf.d && \
    echo "opcache.enable=1\n" \
    "opcache.memory_consumption=256\n" \
    "opcache.interned_strings_buffer=16\n" \
    "opcache.max_accelerated_files=20000\n" \
    "opcache.validate_timestamps=0\n" \
    "opcache.log=/var/log/php/opcache.log\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

COPY --from=vendor /var/www/vendor /var/www/vendor
COPY --from=assets /var/www/public/build /var/www/public/build
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache /var/www && \
    chmod -R 775 storage bootstrap/cache

COPY docker/php/entrypoint.prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
