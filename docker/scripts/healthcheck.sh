#!/bin/bash
set -euo pipefail

APP_DIR="/storage/www/app"
COMPOSE="docker compose -f docker/docker-compose.prod.yml --project-directory ${APP_DIR}"

cd "$APP_DIR"

echo "==> Container status"
$COMPOSE ps

echo "==> HTTP smoke test (http://127.0.0.1/up)"
curl --fail --silent --show-error http://127.0.0.1/up > /dev/null

echo "==> Healthy"
