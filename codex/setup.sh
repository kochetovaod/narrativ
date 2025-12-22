#!/usr/bin/env bash
set -euo pipefail

cd src

# 1) PHP deps
composer install --no-interaction --prefer-dist

# 2) Env (без секретов)
if [ ! -f .env ]; then
  cp .env.example .env
fi

# 3) Use sqlite in Codex Cloud/Web (no external DB)
# Create sqlite file + set env (idempotent)
mkdir -p database
touch database/database.sqlite

# Ensure .env contains sqlite config (append if missing)
grep -q "^DB_CONNECTION=" .env || echo "DB_CONNECTION=sqlite" >> .env
grep -q "^DB_DATABASE=" .env   || echo "DB_DATABASE=$(pwd)/database/database.sqlite" >> .env

# 4) App key + caches
php artisan key:generate --force || true
php artisan optimize:clear || true

# 5) Migrations (safe in isolated env)
php artisan migrate --force || true

# 6) Frontend assets (if project uses Vite)
if [ -f package.json ]; then
  npm ci
  npm run build
fi
