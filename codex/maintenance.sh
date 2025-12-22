#!/usr/bin/env bash
set -euo pipefail

cd src

composer install --no-interaction --prefer-dist
php artisan optimize:clear || true

if [ -f package.json ]; then
  npm ci
  npm run build
fi
