# syntax=docker/dockerfile:1

# ---- base: php-fpm + 拡張 ----
FROM php:8.4-fpm-alpine AS base
RUN apk add --no-cache git unzip libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql bcmath mbstring zip opcache pcntl
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# ---- dev: コードは bind mount で渡す ----
FROM base AS dev
ENV COMPOSER_ALLOW_SUPERUSER=1

# ---- vendor: 本番用の依存をインストール ----
FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ---- prod: コードをイメージに焼き込む ----
FROM base AS prod
COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
