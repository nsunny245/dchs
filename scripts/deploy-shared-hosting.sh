#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${DGC_APP_DIR:-$(cd "${SCRIPT_DIR}/.." && pwd)}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-}"

cd "${APP_DIR}"

if [[ ! -f artisan || ! -d .git ]]; then
    echo "Deployment stopped: ${APP_DIR} is not a Laravel Git checkout." >&2
    exit 1
fi

if ! command -v "${PHP_BIN}" >/dev/null 2>&1 && [[ ! -x "${PHP_BIN}" ]]; then
    echo "Deployment stopped: PHP binary '${PHP_BIN}' was not found." >&2
    exit 1
fi

declare -a COMPOSER_COMMAND

if [[ -n "${COMPOSER_BIN}" ]]; then
    if command -v "${COMPOSER_BIN}" >/dev/null 2>&1 || [[ -x "${COMPOSER_BIN}" ]]; then
        COMPOSER_COMMAND=("${COMPOSER_BIN}")
    elif [[ -f "${COMPOSER_BIN}" ]]; then
        COMPOSER_COMMAND=("${PHP_BIN}" "${COMPOSER_BIN}")
    else
        echo "Deployment stopped: configured Composer path '${COMPOSER_BIN}' was not found." >&2
        exit 1
    fi
elif command -v composer >/dev/null 2>&1; then
    COMPOSER_COMMAND=("composer")
elif [[ -x /opt/cpanel/composer/bin/composer ]]; then
    COMPOSER_COMMAND=("/opt/cpanel/composer/bin/composer")
elif [[ -f "${APP_DIR}/composer.phar" ]]; then
    COMPOSER_COMMAND=("${PHP_BIN}" "${APP_DIR}/composer.phar")
elif [[ -f "${APP_DIR}/../composer.phar" ]]; then
    COMPOSER_COMMAND=("${PHP_BIN}" "${APP_DIR}/../composer.phar")
elif [[ -f "${HOME}/composer.phar" ]]; then
    COMPOSER_COMMAND=("${PHP_BIN}" "${HOME}/composer.phar")
else
    echo "Deployment stopped: Composer was not found." >&2
    echo "Install composer.phar once, or set COMPOSER_BIN to its full path." >&2
    exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "Deployment stopped: the live checkout has uncommitted changes." >&2
    exit 1
fi

bring_application_up() {
    "${PHP_BIN}" artisan up >/dev/null 2>&1 || true
}

trap bring_application_up EXIT

echo "Enabling maintenance mode..."
"${PHP_BIN}" artisan down --retry=60 || true

echo "Updating ${DEPLOY_BRANCH} from GitHub..."
git fetch origin "${DEPLOY_BRANCH}"
git checkout "${DEPLOY_BRANCH}"
git pull --ff-only origin "${DEPLOY_BRANCH}"

echo "Installing production PHP dependencies..."
"${COMPOSER_COMMAND[@]}" install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

echo "Clearing stale Laravel caches..."
"${PHP_BIN}" artisan optimize:clear

echo "Applying pending, non-destructive database migrations..."
"${PHP_BIN}" artisan migrate --force

echo "Synchronizing application roles and permissions..."
"${PHP_BIN}" artisan db:seed --class=RoleAndPermissionSeeder --force
"${PHP_BIN}" artisan db:seed --class=StaffPermissionsSeeder --force
"${PHP_BIN}" artisan permission:cache-reset

echo "Synchronizing the production fee catalog..."
"${PHP_BIN}" artisan db:seed --class=ProductionFeeCatalogSeeder --force

echo "Ensuring the public storage link exists..."
if [ ! -e public/storage ]; then
    "${PHP_BIN}" artisan storage:link || true
fi

echo "Rebuilding production caches..."
"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache

bring_application_up
trap - EXIT

echo "Deployment completed successfully."
