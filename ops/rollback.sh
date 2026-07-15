#!/bin/bash
# Finzo Rollback Script
# Reverts the current symlink to the previous release.

set -e

ENV=$1
BASE_DIR="/var/www/finzo"

if [ "$ENV" == "staging" ]; then
    BASE_DIR="/var/www/finzo-staging"
fi

RELEASES_DIR="${BASE_DIR}/releases"
CURRENT_DIR="${BASE_DIR}/current"

# Find the current release
CURRENT_RELEASE=$(readlink "$CURRENT_DIR")

# Find the previous release
PREVIOUS_RELEASE=$(ls -dt $RELEASES_DIR/* | grep -v "$CURRENT_RELEASE" | head -n 1)

if [ -z "$PREVIOUS_RELEASE" ]; then
    echo "Error: No previous release found to rollback to."
    exit 1
fi

echo "Rolling back to $PREVIOUS_RELEASE..."

# Swap the symlink back
ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_DIR"

# Restart PHP-FPM
sudo systemctl reload php8.3-fpm

echo "Rollback successful!"
