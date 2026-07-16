#!/usr/bin/env bash
# Finzo Rollback Script
# Reverts the current symlink to the previous release.

# Strict error handling
set -euo pipefail

if [ -z "${1:-}" ]; then
    echo "Error: Missing required argument."
    echo "Usage: $0 <environment>"
    exit 1
fi

ENV=$1

# Define Base Directory
if [ "$ENV" == "staging" ]; then
    BASE_DIR="/var/www/finzo-staging"
elif [ "$ENV" == "production" ]; then
    BASE_DIR="/var/www/finzo"
else
    echo "Error: Invalid environment. Must be 'staging' or 'production'."
    exit 1
fi

RELEASES_DIR="${BASE_DIR}/releases"
CURRENT_DIR="${BASE_DIR}/current"

if [ ! -L "$CURRENT_DIR" ]; then
    echo "Error: Current release symlink not found."
    exit 1
fi

# Find the absolute path of the current release
CURRENT_RELEASE=$(readlink "$CURRENT_DIR")
CURRENT_BASENAME=$(basename "$CURRENT_RELEASE")

echo "Current release is $CURRENT_BASENAME"

# Find the previous release by listing releases sorted by modification time 
# and excluding the current release.
cd "$RELEASES_DIR"
PREVIOUS_BASENAME=$(ls -1t | grep -v "^${CURRENT_BASENAME}$" | head -n 1)

if [ -z "$PREVIOUS_BASENAME" ]; then
    echo "Error: No previous release found to rollback to."
    exit 1
fi

PREVIOUS_RELEASE="${RELEASES_DIR}/${PREVIOUS_BASENAME}"

echo "Rolling back to $PREVIOUS_RELEASE..."

# Swap the symlink back
ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_DIR"

# Restart PHP-FPM
echo "Restarting PHP-FPM..."
if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl reload php8.3-fpm || echo "Warning: Failed to reload PHP-FPM."
else
    echo "Warning: systemctl not found. PHP-FPM reload skipped."
fi

echo "Rollback to ${PREVIOUS_BASENAME} successful!"
