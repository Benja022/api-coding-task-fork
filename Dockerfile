FROM php:8.2-fpm-alpine AS base

RUN apk --update add \
    alpine-sdk \
    linux-headers \
    openssl-dev \
    php82-pear \
    php82-dev

RUN docker-php-ext-install pdo_mysql

RUN rm -rf /var/cache/apk/*

RUN pecl install redis

RUN docker-php-ext-enable redis

EXPOSE 9000

FROM base AS development

ENV TZ=UTC

RUN pecl channel-update pecl.php.net

RUN apk add --update --upgrade tzdata autoconf g++ make \
    && ln -s /usr/share/zoneinfo/$TZ /etc/localtime \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer