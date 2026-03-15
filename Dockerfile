FROM php:8.4-fpm-alpine

RUN apk add --no-cache git unzip bash \
    && docker-php-ext-install pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
