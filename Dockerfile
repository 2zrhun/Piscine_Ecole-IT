# Étape 1 : Builder
FROM php:8.2-apache AS builder

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libxml2-dev \
 && docker-php-ext-install pdo pdo_mysql zip intl opcache \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier uniquement composer pour installer les dépendances
COPY API/composer.json API/composer.lock ./
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts

# Copier le reste du code
COPY API/. .

# Clear & warmup cache
RUN php bin/console cache:clear --env=prod --no-debug || true \
 && php bin/console cache:warmup --env=prod || true

# Étape 2 : Image finale
FROM php:8.2-apache

RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copier depuis le builder
COPY --from=builder /app /var/www/html

# Permissions pour var/
RUN mkdir -p var && chown -R www-data:www-data var vendor

EXPOSE 80

CMD ["apache2-foreground"]
