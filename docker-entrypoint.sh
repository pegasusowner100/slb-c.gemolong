#!/bin/sh
set -e

PORT="${PORT:-8080}"

# Generate config.php from environment variables
/generate-config.sh

# Update Apache to listen on the Railway-assigned PORT
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

exec apache2ctl -D FOREGROUND
