FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libzip-dev libcurl4-openssl-dev unzip git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd zip curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data . /var/www/html/

EXPOSE 80
CMD ["apache2-foreground"]
