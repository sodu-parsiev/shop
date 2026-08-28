# Local dev Docker stack

PHP 8.4-FPM + Nginx + MySQL 8 for local development. Node/Vite run on the host
(`npm run dev`), not in a container.

All commands below are run from the repo root.

## First-time setup

```
docker compose -f docker/docker-compose.yml up -d --build
docker compose -f docker/docker-compose.yml exec app composer install
docker compose -f docker/docker-compose.yml exec app php artisan key:generate
docker compose -f docker/docker-compose.yml exec app php artisan migrate
```

The app is served at http://localhost:8080.

## Everyday commands

```
docker compose -f docker/docker-compose.yml exec app php artisan <command>
docker compose -f docker/docker-compose.yml exec app composer <command>
docker compose -f docker/docker-compose.yml exec app php artisan test
```

## Services

| Service | Purpose                | Host access               |
|---------|-------------------------|----------------------------|
| app     | PHP 8.4-FPM             | -                          |
| nginx   | Webserver                | http://localhost:8080     |
| mysql   | MySQL 8                  | localhost:3307 (db `shop`, user `shop`, password `secret`) |

Credentials are dev-only defaults, set in `docker/docker-compose.yml`.

## Stopping / resetting

```
docker compose -f docker/docker-compose.yml down       # stop
docker compose -f docker/docker-compose.yml down -v    # stop + wipe MySQL data
```

## File ownership

The `app` image's `www-data` user is built with UID/GID 1000 by default (the
common first-user id on Linux), so files it writes into the bind-mounted repo
(`storage/`, `bootstrap/cache/`) are owned by the host user. If your host user
has a different UID/GID, rebuild with:

```
DOCKER_UID=$(id -u) DOCKER_GID=$(id -g) docker compose -f docker/docker-compose.yml build
```

## Production

Production uses a separate, self-contained stack — nothing above is used in
production and vice versa:

- `docker/php/Dockerfile.prod` — multi-stage build (Composer deps, Vite
  assets, final PHP-FPM image with app source baked in; no bind mounts).
- `docker/nginx/prod.conf` — serves on port 80 for any hostname
  (`default_server`), ready for a real domain + TLS later.
- `docker/docker-compose.prod.yml` — `nginx` (published on `:80`) + `app` +
  `mysql` (both internal-only), named volumes for MySQL data and Laravel
  `storage/`.
- `docker/scripts/deploy.sh` / `docker/scripts/healthcheck.sh` — build, apply,
  migrate, and verify a deployment; run on the VPS by
  `.github/workflows/deploy.yml` on every push to `main`.

Deployment is driven by GitHub Actions over SSH — see
`.github/workflows/deploy.yml` for the flow and the repo secrets it expects
(`DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_SSH_KEY`). To deploy or roll back
manually from the VPS:

```
/storage/www/app/docker/scripts/deploy.sh <git-sha>
```
