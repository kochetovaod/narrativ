# AGENTS.md (project instructions)

## Repository layout
- Laravel application lives in: `src/`
- Docker Compose is at repo root (`docker-compose.yml`).
- Use `docker compose exec app ...` for all PHP/Composer/Artisan commands.

## Core commands (always run from repo root)
### Start environment
- Build & start: `docker compose up -d --build`
- Stop: `docker compose down`

### Laravel
- Install PHP deps: `docker compose exec app composer install`
- Artisan: `docker compose exec app php artisan <cmd>`
- Migrations: `docker compose exec app php artisan migrate`
- Clear caches: `docker compose exec app php artisan optimize:clear`

### Frontend (if needed)
- Install node deps: `docker compose exec app npm ci`
- Build: `docker compose exec app npm run build`
- Dev: `docker compose exec app npm run dev`

## Testing / quality gates
- After backend changes run:
  - `docker compose exec app php artisan test` (if tests exist)
  - `docker compose exec app php artisan migrate --pretend` (if migration changes)
- Use `laravel/pint` when introduced: `docker compose exec app ./vendor/bin/pint`

## Project rules (must follow)
### Draft preview security
- Draft preview routes MUST be admin-only (auth/Filament guard).
- Preview responses MUST include `X-Robots-Tag: noindex, nofollow, noarchive`.
- `robots.txt` MUST disallow `/preview`.
- Never add preview URLs to sitemap.

### Content model conventions
For all content entities:
- Must include: title, slug, seo_title, seo_description, is_published, published_at
- Must include author stamps: created_by, updated_by, deleted_by + soft deletes.
- Media uploads must enforce per-image `alt` and `title` (custom properties).

## When unsure
- Prefer editing/adding migration + Filament Resource in small commits.
- Do not introduce new dependencies without checking compatibility with Filament v3.
