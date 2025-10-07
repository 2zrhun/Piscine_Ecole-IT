FROM httpd:2.4
COPY ./ /usr/local/apache2/htdocs/
COPY ./apache/httpd.conf /usr/local/apache2/conf/httpd.conf
EXPOSE 80
CMD ["httpd-foreground"]


FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev git unzip zip \
    && docker-php-ext-install pdo pdo_pgsql && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip \
    opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY ./ /var/www/html
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader
RUN a2enmod rewrite
EXPOSE 80
CMD ["apache2-foreground"]


