#!/bin/sh
set -e

PORT="${PORT:-8080}"

# Generate config.php from environment variables
/generate-config.sh

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
