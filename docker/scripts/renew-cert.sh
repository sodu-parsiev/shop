#!/bin/bash
# Run twice daily via cron on the VPS (certbot's own recommendation).
# `certbot renew` is a no-op unless the cert is within 30 days of expiry.
set -euo pipefail

APP_DIR="/storage/www/app"
COMPOSE="docker compose -f docker/docker-compose.prod.yml --project-directory ${APP_DIR}"

cd "$APP_DIR"

$COMPOSE exec -T certbot certbot renew --webroot -w /var/www/certbot --quiet
$COMPOSE exec -T nginx nginx -s reload
