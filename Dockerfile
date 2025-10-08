FROM php:8.2-apache AS builder

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
 && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip \
    opcache \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite headers

WORKDIR /var/www/html/Piscine_Ecole-IT

COPY API/composer.json API/composer.lock ./

RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts

COPY API/.env .env
ENV APP_ENV=prod


RUN php bin/console cache:clear --env=prod --no-debug || true \
 && php bin/console cache:warmup --env=prod || true

FROM php:8.2-apache

RUN a2enmod rewrite headers

COPY --from=builder /var/www/html /var/www/html

WORKDIR /var/www/html

RUN mkdir -p var && chown -R www-data:www-data var || true

EXPOSE 80

CMD ["apache2-foreground"]