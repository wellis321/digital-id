# Setting Up Test Database

## Quick Setup (Recommended)

### Option 1: PHP Script (Works without MySQL in PATH)

If you don't have MySQL command line tools in your PATH, use the PHP script:

```bash
php tests/setup-test-db.php
```

This uses your existing database configuration from `config/database.php` and will:
1. Create the `digital_ids_test` database
2. Run the complete schema
3. Run any migrations
4. Verify tables were created

### Option 2: Shell Script (Requires MySQL in PATH)

If you have MySQL command line tools available:

```bash
./tests/setup-test-db.sh
```

**Note:** If you get "mysql: command not found", use Option 1 instead.

## Manual Setup

If you prefer to set it up manually:

### 1. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE digital_ids_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 2. Run the complete schema

The `complete_schema.sql` file includes all tables in the correct order:

```bash
mysql -u root -p digital_ids_test < sql/complete_schema.sql
```

Or using MySQL command line:

```bash
mysql -u root -p
```

Then:

```sql
USE digital_ids_test;
SOURCE sql/complete_schema.sql;
```

### 3. Verify tables were created

```bash
mysql -u root -p digital_ids_test -e "SHOW TABLES;"
```

You should see tables like:
- `organisations`
- `users`
- `roles`
- `user_roles`
- `employees`
- `digital_id_cards`
- `verification_logs`
- `check_in_sessions`
- etc.

## Troubleshooting

### "mysql: command not found" error

This means MySQL command line tools aren't in your PATH. Use the PHP script instead:

```bash
php tests/setup-test-db.php
```

The PHP script uses your existing database configuration, so it doesn't require MySQL in your PATH.

### "Table 'users' doesn't exist" error

This means the core authentication tables weren't created. The setup script uses `complete_schema.sql` which includes all tables. If you're still getting this error, make sure you're using the latest version of the setup script.

### "Access denied" error

Make sure you're using the correct MySQL username and password. The PHP script uses your existing database configuration from `config/database.php` or `.env` file.

You can also set them as environment variables:

```bash
export DB_USER=your_username
export DB_PASS=your_password
export DB_HOST=localhost
php tests/setup-test-db.php
```

### Database already exists

The setup script will create the database if it doesn't exist. If you want to drop and recreate it, you can manually drop it first:

```sql
DROP DATABASE IF EXISTS digital_ids_test;
```

Then run the setup script again.

### MySQL not running

Make sure MySQL/MariaDB is running on your system. You can check with:

```bash
# macOS with Homebrew
brew services list

# Or check if MySQL is listening
lsof -i :3306
```

## Environment Variables

You can customize the database connection:

```bash
export DB_NAME=digital_ids_test
export DB_USER=root
export DB_PASS=your_password
export DB_HOST=localhost

./tests/setup-test-db.sh
```

## Next Steps

After setting up the database:

1. Update `phpunit.xml` with your database credentials
2. Run tests: `./vendor/bin/phpunit`
3. Check test results

