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
- `docker/scripts/deploy.sh` / `docker/scripts/healthcheck.sh` — run on the
  VPS itself; build, apply, migrate, and verify a deployment.
- `docker/scripts/trigger-deploy.sh` — **run this from your own machine to
  deploy.**

Deployment is triggered manually from a developer machine, not CI — the
VPS's network path is intermittently unreachable for minutes at a time
(confirmed independently of load; the box itself stays idle/healthy through
it), which made an unattended GitHub Actions runner an unreliable place to
sit through a retry loop. `trigger-deploy.sh` runs the same idempotent
build → apply → migrate → smoke-test sequence with a 10-attempt retry loop
you can watch and let ride out a bad window:

```
docker/scripts/trigger-deploy.sh              # push current HEAD and deploy it
docker/scripts/trigger-deploy.sh <git-sha>     # deploy/rollback to a specific commit
```

It expects the `github_actions_deploy` private key in the repo root (or
`DEPLOY_SSH_KEY` pointing elsewhere). To deploy or roll back directly from
the VPS itself instead:

```
/storage/www/app/docker/scripts/deploy.sh <git-sha>
```
