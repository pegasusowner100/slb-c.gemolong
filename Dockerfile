FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libzip-dev libcurl4-openssl-dev unzip git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd zip curl \
    && a2enmod rewrite \
    && rm -rf /etc/apache2/mods-available/mpm_event* \
    && rm -rf /etc/apache2/mods-available/mpm_worker* \
    && rm -rf /etc/apache2/mods-enabled/mpm_event* \
    && rm -rf /etc/apache2/mods-enabled/mpm_worker* \
    && a2enmod mpm_prefork \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

EXPOSE 80
CMD ["apache2-foreground"]
