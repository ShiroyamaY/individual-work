FROM php:8.3-cli

RUN apt-get update &&  apt-get install -y libpq-dev
RUN docker-php-ext-install pdo && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install ctype

WORKDIR /var/www/vsign

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring zip exif pcntl gd opentelemetry grpc
RUN echo "memory_limit=1024M" > /usr/local/etc/php/conf.d/memory-limit.ini

RUN chown -R www-data:www-data /var/www/vsign

ENTRYPOINT ["php", "artisan"]
CMD ["--help"]