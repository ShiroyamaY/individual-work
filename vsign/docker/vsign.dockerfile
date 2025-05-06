FROM composer:2.2.25 AS vendor

WORKDIR /var/www/vsign

COPY application/composer.json application/composer.lock ./

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM node:23-alpine AS front-build

WORKDIR /var/www/vsign-client

COPY client/package*.json ./

RUN npm install

COPY client/ ./

RUN npm run build

FROM php:8.3-fpm AS vsign_final

EXPOSE 9000

WORKDIR /var/www/vsign

COPY --from=vendor /var/www/vsign/vendor /var/www/vsign/vendor
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions grpc
RUN install-php-extensions opentelemetry redis
RUN apt-get update && apt-get install -y \
    ghostscript \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

COPY --chown=www-data:www-data --from=front-build /var/www/vsign-client/dist /var/www/vsign-client/dist
COPY --chown=www-data:www-data --chmod=755 application/ .
COPY --chmod=555 docker/vsign_boot.sh .

CMD ["php-fpm"]