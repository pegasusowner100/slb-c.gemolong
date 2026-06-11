FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive
ENV PORT=8080

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
        zlib1g-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip curl \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/
COPY docker-entrypoint.sh /docker-entrypoint.sh
COPY generate-config.sh /generate-config.sh

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

RUN chmod +x /docker-entrypoint.sh /generate-config.sh

EXPOSE 8080
CMD ["/docker-entrypoint.sh"]
