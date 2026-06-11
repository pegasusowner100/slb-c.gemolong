FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libzip-dev libcurl4-openssl-dev unzip git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd zip curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Fix ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure Apache for dynamic PORT from Railway
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY . /var/www/html/

EXPOSE 80
CMD ["apache2-foreground"]
