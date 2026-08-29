#!/bin/bash
# One-time bootstrap: obtain the initial Let's Encrypt certificate.
# Run on the VPS after deploying with the ACME-challenge nginx location and
# the `certbot` service in place, but BEFORE nginx's config references the
# certificate files (they don't exist yet on a first run).
set -euo pipefail

APP_DIR="/storage/www/app"
DOMAIN="194-87-221-164.sslip.io"
COMPOSE="docker compose -f docker/docker-compose.prod.yml --project-directory ${APP_DIR}"

cd "$APP_DIR"

echo "==> Requesting certificate for ${DOMAIN}"
$COMPOSE exec -T certbot certbot certonly \
    --webroot -w /var/www/certbot \
    -d "$DOMAIN" \
    --register-unsafely-without-email \
    --agree-tos \
    --non-interactive

echo "==> Certificate obtained. Now add the HTTPS server block to"
echo "    docker/nginx/prod.conf and redeploy."
