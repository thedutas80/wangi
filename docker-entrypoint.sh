#!/bin/sh
set -e

# Substitute PORT in Nginx config
envsubst '${PORT}' < /etc/nginx/sites-enabled/default > /tmp/nginx.conf
mv /tmp/nginx.conf /etc/nginx/sites-enabled/default

# Execute CMD
exec "$@"
