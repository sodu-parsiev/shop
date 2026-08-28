#!/bin/bash
# Runs on the VPS (as the `deploy` user) via GitHub Actions.
# Usage: deploy.sh <git-sha>
set -euo pipefail

SHA="${1:?usage: deploy.sh <git-sha>}"
APP_DIR="/storage/www/app"
COMPOSE="docker compose -f docker/docker-compose.prod.yml --project-directory ${APP_DIR}"

cd "$APP_DIR"

echo "==> Fetching and checking out ${SHA}"
git fetch origin
git checkout main
git reset --hard "$SHA"

echo "==> Building images"
$COMPOSE build

echo "==> Applying deployment"
$COMPOSE up -d --remove-orphans

echo "==> Waiting for app container to accept exec"
for i in $(seq 1 15); do
    if $COMPOSE exec -T app php -v >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

echo "==> Running Laravel deployment commands"
$COMPOSE exec -T app php artisan migrate --force
$COMPOSE exec -T app php artisan optimize

echo "==> Deployment complete, running health check"
"$(dirname "$0")/healthcheck.sh"
