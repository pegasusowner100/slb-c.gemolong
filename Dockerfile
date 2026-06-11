FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive
ENV PORT=8080

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        apache2 \
        php8.2 \
        libapache2-mod-php8.2 \
        php8.2-gd \
        php8.2-zip \
        php8.2-curl \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-mysql \
        ca-certificates \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/*

# Fix ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure Apache for dynamic PORT from Railway (handled at container startup)
# Port substitution is performed by /docker-entrypoint.sh at runtime

# Use mpm_prefork exclusively (required for mod_php)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod php8.2 \
    && a2enmod rewrite

# Allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

COPY docker-entrypoint.sh /docker-entrypoint.sh
COPY generate-config.sh /generate-config.sh
RUN chmod +x /docker-entrypoint.sh /generate-config.sh

EXPOSE 8080
CMD ["/docker-entrypoint.sh"]
