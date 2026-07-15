#!/bin/bash
# Finzo Zero-Downtime Deployment Script
# Usage: ./deploy.sh [environment] [archive.tar.gz]

set -e

ENV=$1
ARCHIVE=$2
TIMESTAMP=$(date +%Y%m%d%H%M%S)
BASE_DIR="/var/www/finzo"

if [ "$ENV" == "staging" ]; then
    BASE_DIR="/var/www/finzo-staging"
fi

RELEASES_DIR="${BASE_DIR}/releases"
SHARED_DIR="${BASE_DIR}/shared"
CURRENT_DIR="${BASE_DIR}/current"
NEW_RELEASE="${RELEASES_DIR}/${TIMESTAMP}"

echo "Starting Deployment for $ENV..."

# Create directories
mkdir -p "$RELEASES_DIR"
mkdir -p "$SHARED_DIR/storage/logs"
mkdir -p "$SHARED_DIR/storage/sessions"
mkdir -p "$NEW_RELEASE"

# Extract archive
tar -xzf "$ARCHIVE" -C "$NEW_RELEASE"

# Symlink shared directories (storage, env)
ln -s "$SHARED_DIR/.env" "$NEW_RELEASE/.env"
rm -rf "$NEW_RELEASE/storage"
ln -s "$SHARED_DIR/storage" "$NEW_RELEASE/storage"

# Run Migrations (if applicable for PHP PDO setup)
# php $NEW_RELEASE/ops/migrate.php

# Swap the symlink
ln -sfn "$NEW_RELEASE" "$CURRENT_DIR"

# Restart PHP-FPM to clear OpCache
sudo systemctl reload php8.3-fpm

# Clean up old releases (keep last 5)
ls -dt $RELEASES_DIR/* | tail -n +6 | xargs -d '\n' rm -rf --

echo "Deployment $TIMESTAMP successful!"
