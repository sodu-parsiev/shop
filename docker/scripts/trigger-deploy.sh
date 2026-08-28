#!/bin/bash
# Run this from your OWN machine (not the VPS) to trigger a deployment.
#
# Exists because the VPS (Timeweb, Moscow) has an intermittently unreachable
# network path lasting minutes at a time — GitHub Actions' hosted runners hit
# it just as often as anywhere else and have no one watching to retry past a
# bad window. Running from a machine you control lets you see what's
# happening and keep retrying past the flaky stretches.
#
# Usage:
#   docker/scripts/trigger-deploy.sh              # push current HEAD and deploy it
#   docker/scripts/trigger-deploy.sh <git-sha>     # deploy a specific commit already on GitHub (e.g. rollback)
#
# Env overrides: DEPLOY_HOST, DEPLOY_USER, DEPLOY_SSH_KEY
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DEPLOY_HOST="${DEPLOY_HOST:-217.149.29.64}"
DEPLOY_USER="${DEPLOY_USER:-deploy}"
DEPLOY_SSH_KEY="${DEPLOY_SSH_KEY:-$REPO_ROOT/github_actions_deploy}"

if [ ! -f "$DEPLOY_SSH_KEY" ]; then
    echo "SSH key not found at $DEPLOY_SSH_KEY (set DEPLOY_SSH_KEY to override)" >&2
    exit 1
fi

if [ $# -ge 1 ]; then
    SHA="$1"
else
    SHA="$(git -C "$REPO_ROOT" rev-parse HEAD)"
    echo "==> Pushing ${SHA} to origin/main"
    git -C "$REPO_ROOT" push origin HEAD:main
fi

echo "==> Deploying ${SHA} to ${DEPLOY_USER}@${DEPLOY_HOST}"

max_attempts=10
attempt=0
success=false
while [ "$attempt" -lt "$max_attempts" ]; do
    attempt=$((attempt + 1))
    echo "--- SSH attempt ${attempt}/${max_attempts} ---"
    if timeout 600 ssh -o BatchMode=yes -o ConnectTimeout=20 -o StrictHostKeyChecking=accept-new \
            -o ServerAliveInterval=10 -o ServerAliveCountMax=3 \
            -i "$DEPLOY_SSH_KEY" \
            "${DEPLOY_USER}@${DEPLOY_HOST}" \
            "/storage/www/app/docker/scripts/deploy.sh '${SHA}'"; then
        success=true
        break
    fi
    echo "Attempt ${attempt} failed or timed out."
    if [ "$attempt" -lt "$max_attempts" ]; then
        echo "Retrying in 20s..."
        sleep 20
    fi
done

if [ "$success" != true ]; then
    echo "All ${max_attempts} attempts failed — the VPS may be in an extended unreachable window, try again shortly." >&2
    exit 1
fi

echo "==> Deploy succeeded, running external smoke test"
for i in $(seq 1 5); do
    if curl --fail --silent --show-error --max-time 20 "http://${DEPLOY_HOST}/up" > /dev/null; then
        echo "==> Live at http://${DEPLOY_HOST}"
        exit 0
    fi
    echo "Smoke test attempt ${i} failed, retrying in 10s..."
    sleep 10
done

echo "Deployed, but the external smoke test didn't succeed within the retry window (likely VPS network flakiness) — verify manually." >&2
exit 1
