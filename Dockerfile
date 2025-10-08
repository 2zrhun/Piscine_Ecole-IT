FROM php:8.3-apache

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

COPY API .

RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts

RUN composer --version

COPY API/.env .env
ENV APP_ENV=prod


RUN php bin/console cache:clear --env=prod --no-debug \
 && php bin/console cache:warmup --env=prod

RUN a2enmod rewrite headers

RUN mkdir -p var && chown -R www-data:www-data var

EXPOSE 80

CMD ["apache2-foreground"]