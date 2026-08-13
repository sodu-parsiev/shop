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
