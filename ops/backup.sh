#!/bin/bash
# Finzo Backup Script

set -e

BACKUP_DIR="/var/www/finzo/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="finzo_db"
DB_USER="finzo"
DB_PASS="secret" # In production, read this securely from .env

mkdir -p "$BACKUP_DIR"

echo "Starting Finzo backup: $TIMESTAMP"

# Database Backup
echo "Dumping database..."
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/db_$TIMESTAMP.sql"

# Storage Backup
echo "Archiving storage..."
tar -czf "$BACKUP_DIR/storage_$TIMESTAMP.tar.gz" -C /var/www/finzo/shared/storage .

# Compress together
echo "Creating final archive..."
tar -czf "$BACKUP_DIR/finzo_full_$TIMESTAMP.tar.gz" -C "$BACKUP_DIR" "db_$TIMESTAMP.sql" "storage_$TIMESTAMP.tar.gz"

# Cleanup intermediate files
rm "$BACKUP_DIR/db_$TIMESTAMP.sql"
rm "$BACKUP_DIR/storage_$TIMESTAMP.tar.gz"

echo "Backup completed successfully: $BACKUP_DIR/finzo_full_$TIMESTAMP.tar.gz"
