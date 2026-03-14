#!/usr/bin/env sh

set -e

cd /var/www/backend

if [ -f "composer.json" ]; then
    echo "Generating Composer autoload files..."
    composer dump-autoload
fi

exec "$@"