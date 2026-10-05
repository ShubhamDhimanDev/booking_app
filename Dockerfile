FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev unzip libicu-dev libonig-dev libxml2-dev libpng-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath intl zip gd xml \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
