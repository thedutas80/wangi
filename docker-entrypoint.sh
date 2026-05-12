#!/bin/sh
set -e

PORT="${PORT:-80}"

sed -i "s/__PORT__/$PORT/g" /etc/nginx/sites-enabled/default

exec "$@"
