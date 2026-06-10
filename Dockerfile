FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libzip-dev libcurl4-openssl-dev unzip git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd zip curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
