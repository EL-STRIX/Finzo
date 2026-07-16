#!/usr/bin/env bash
# Finzo Zero-Downtime Deployment Script
# Usage: ./deploy.sh <environment> <archive.tar.gz>

# Strict error handling
set -euo pipefail

if [ "$#" -lt 2 ]; then
    echo "Error: Missing required arguments."
    echo "Usage: $0 <environment> <archive.tar.gz>"
    exit 1
fi

ENV=$1
ARCHIVE=$2
TIMESTAMP=$(date +%Y%m%d%H%M%S)

# Define Base Directory
if [ "$ENV" == "staging" ]; then
    BASE_DIR="/var/www/finzo-staging"
elif [ "$ENV" == "production" ]; then
    BASE_DIR="/var/www/finzo"
else
    echo "Error: Invalid environment. Must be 'staging' or 'production'."
    exit 1
fi

if [ ! -f "$ARCHIVE" ]; then
    echo "Error: Archive file '$ARCHIVE' does not exist."
    exit 1
fi

RELEASES_DIR="${BASE_DIR}/releases"
SHARED_DIR="${BASE_DIR}/shared"
CURRENT_DIR="${BASE_DIR}/current"
NEW_RELEASE="${RELEASES_DIR}/${TIMESTAMP}"

echo "Starting Deployment for $ENV..."

# Create necessary directories
mkdir -p "$RELEASES_DIR"
mkdir -p "${SHARED_DIR}/storage/logs"
mkdir -p "${SHARED_DIR}/storage/sessions"
mkdir -p "$NEW_RELEASE"

# Extract archive
echo "Extracting archive to $NEW_RELEASE..."
tar -xzf "$ARCHIVE" -C "$NEW_RELEASE"

# Symlink shared directories and .env
echo "Symlinking shared resources..."
if [ -f "${SHARED_DIR}/.env" ]; then
    ln -sfn "${SHARED_DIR}/.env" "${NEW_RELEASE}/.env"
else
    echo "Warning: Shared .env file not found at ${SHARED_DIR}/.env"
fi

rm -rf "${NEW_RELEASE}/storage"
ln -sfn "${SHARED_DIR}/storage" "${NEW_RELEASE}/storage"

# Swap the symlink to make it live
echo "Activating new release..."
ln -sfn "$NEW_RELEASE" "$CURRENT_DIR"

# Restart PHP-FPM to clear OpCache
echo "Restarting PHP-FPM..."
if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl reload php8.3-fpm || echo "Warning: Failed to reload PHP-FPM."
else
    echo "Warning: systemctl not found. PHP-FPM reload skipped."
fi

# Clean up old releases (keep last 5)
echo "Cleaning up old releases..."
cd "$RELEASES_DIR"
# List directories sorted by modification time (oldest first), skip the 5 newest, and delete the rest
ls -1t | tail -n +6 | xargs -I {} rm -rf "{}"

echo "Deployment $TIMESTAMP completed successfully!"
