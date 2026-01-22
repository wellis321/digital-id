#!/bin/bash
# Setup script for test database
# Usage: ./tests/setup-test-db.sh

set -e

DB_NAME="${DB_NAME:-digital_ids_test}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-localhost}"

echo "Setting up test database: $DB_NAME"

# Create database
mysql -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h "$DB_HOST" -e "DROP DATABASE IF EXISTS $DB_NAME;"
mysql -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h "$DB_HOST" -e "CREATE DATABASE $DB_NAME;"

# Run complete schema (includes all tables in correct order)
echo "Running complete schema..."
if [ -f "sql/complete_schema.sql" ]; then
    mysql -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h "$DB_HOST" "$DB_NAME" < sql/complete_schema.sql
else
    # Fallback to schema.sql if complete_schema.sql doesn't exist
    echo "Warning: complete_schema.sql not found, using schema.sql"
    echo "Note: You may need to run shared-auth migrations first"
    mysql -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h "$DB_HOST" "$DB_NAME" < sql/schema.sql
fi

# Run migrations if they exist
if [ -d "sql/migrations" ]; then
    echo "Running migrations..."
    for migration in sql/migrations/*.sql; do
        if [ -f "$migration" ]; then
            echo "  Running: $migration"
            mysql -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -h "$DB_HOST" "$DB_NAME" < "$migration"
        fi
    done
fi

echo "Test database setup complete!"

