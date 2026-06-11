#!/bin/sh
set -e

echo "ENTRYPOINT: web/docker-entrypoint.sh version 2026-06-11"

echo "CHECK: /generate-config.sh exists?" ; ls -la /generate-config.sh 2>/dev/null || true

PORT="${PORT:-8080}"

# Generate config.php from environment variables
for path in /generate-config.sh /var/www/html/generate-config.sh /usr/src/app/generate-config.sh; do
    if [ -f "$path" ]; then
        echo "Found config generator at $path"
        ls -la "$path" || true
        if [ -x "$path" ]; then
            echo "Executing $path"
            "$path"
        else
            echo "$path is not executable, running with sh"
            sh "$path"
        fi
        break
    fi
done
if [ $? -ne 0 ]; then
    echo "ERROR: config generator failed or not found"
    echo "Looking for fallback locations..."
    ls -la / || true
    ls -la /var/www/html || true
    ls -la /usr/src/app || true
    exit 1
fi

# Disable conflicting Apache MPM modules and enable prefork only
if command -v a2dismod >/dev/null 2>&1; then
    a2dismod mpm_event mpm_worker 2>/dev/null || true
fi
if command -v a2enmod >/dev/null 2>&1; then
    a2enmod mpm_prefork 2>/dev/null || true
fi
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf || true

# Update Apache to listen on the Railway-assigned PORT
if [ -n "${PORT}" ]; then
	if [ -f /etc/apache2/ports.conf ]; then
		sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
	fi
	if [ -f /etc/apache2/sites-enabled/000-default.conf ]; then
		sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf
	fi
fi

exec apache2ctl -D FOREGROUND
