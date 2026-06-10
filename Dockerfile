FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libzip-dev libcurl4-openssl-dev unzip git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip curl \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD sh -c 'sed -i "s/Listen 80/Listen ${PORT:-8080}/g" /etc/apache2/ports.conf && apache2-foreground'
