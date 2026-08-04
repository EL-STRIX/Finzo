#!/bin/bash
# Finzo Deployment Script

set -e

ENV=$1
ARCHIVE=$2

if [ -z "$ENV" ] || [ -z "$ARCHIVE" ]; then
    echo "Usage: $0 <environment> <archive.tar.gz>"
    exit 1
fi

echo "Starting Finzo deployment for environment: $ENV"

DEPLOY_DIR="/var/www/finzo/deployments"
RELEASE_DIR="$DEPLOY_DIR/release_$(date +%Y%m%d%H%M%S)"
CURRENT_LINK="/var/www/finzo/current"
SHARED_DIR="/var/www/finzo/shared"

mkdir -p "$RELEASE_DIR"
echo "Extracting archive to $RELEASE_DIR..."
tar -xzf "$ARCHIVE" -C "$RELEASE_DIR"

echo "Setting up shared resources (storage, .env)..."
mkdir -p "$SHARED_DIR/storage/app"
mkdir -p "$SHARED_DIR/storage/logs"

# Link .env
if [ -f "$SHARED_DIR/.env" ]; then
    ln -s "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
else
    echo "WARNING: .env not found in shared directory. Using .env.example..."
    cp "$RELEASE_DIR/.env.example" "$RELEASE_DIR/.env"
fi

echo "Updating current symlink..."
rm -f "$CURRENT_LINK"
ln -s "$RELEASE_DIR" "$CURRENT_LINK"

echo "Restarting PHP-FPM / Web Server (if configured)..."
# In a real environment, this should reload PHP-FPM
# sudo systemctl reload php8.4-fpm || true

echo "Deployment successful."
