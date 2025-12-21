# Local Development Setup

## Prerequisites

- PHP 7.4+ (you have PHP 8.5.0 ✓)
- MySQL/MariaDB installed and running
- Composer (optional, for future dependencies)

## Step 1: Database Setup

1. **Create the database:**
   ```sql
   CREATE DATABASE digital_id CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Run the complete schema:**
   ```bash
   mysql -u root -p digital_id < sql/complete_schema.sql
   ```
   
   Or if you prefer using MySQL client:
   ```sql
   USE digital_id;
   source sql/complete_schema.sql;
   ```

3. **Optionally, add Entra columns (if needed):**
   ```bash
   mysql -u root -p digital_id < sql/migration_entra_columns.sql
   ```

## Step 2: Environment Configuration

1. **Create `.env` file from example:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` with your database credentials:**
   ```env
   APP_ENV=development
   APP_NAME=Digital ID
   APP_URL=http://localhost:8000

   DB_HOST=localhost
   DB_NAME=digital_id
   DB_USER=root
   DB_PASS=your_password_here
   DB_CHARSET=utf8mb4

   MAIL_FROM=noreply@digitalid.local
   MAIL_REPLY_TO=support@digitalid.local
   ```

## Step 3: Set Up File Permissions

```bash
chmod -R 755 uploads/
```

## Step 4: Run PHP Built-in Server

The easiest way to run PHP locally is using PHP's built-in development server:

```bash
cd /Users/wellis/Desktop/Cursor/digital-id
php -S localhost:8000 -t public
```

This will start the server on `http://localhost:8000`

## Step 5: Access the Application

Open your browser and navigate to:
- **Home:** http://localhost:8000
- **Login:** http://localhost:8000/login.php
- **Register:** http://localhost:8000/register.php

## Quick Start Script

You can also use the provided `start.sh` script:

```bash
chmod +x start.sh
./start.sh
```

## Creating Your First Organisation

Before users can register, you need to create an organisation:

```sql
INSERT INTO organisations (name, domain, seats_allocated) 
VALUES ('Test Organisation', 'example.com', 100);
```

Then users with email addresses ending in `@example.com` can register.

## Creating a Superadmin

To create a superadmin user, you can use this SQL (after creating an organisation):

```sql
-- First, get the organisation ID
SELECT id FROM organisations LIMIT 1;

-- Then create a superadmin (replace ORG_ID with actual ID)
INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, email_verified, is_active)
VALUES (
    1, -- Replace with your organisation ID
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Admin',
    'User',
    TRUE,
    TRUE
);

-- Assign superadmin role
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id 
FROM users u, roles r 
WHERE u.email = 'admin@example.com' AND r.name = 'superadmin';
```

**Default password:** `password` (change this immediately!)

## Troubleshooting

### Database Connection Error
- Check your `.env` file has correct database credentials
- Ensure MySQL is running: `mysql.server start` (on macOS)

### Permission Denied
- Make sure `uploads/` directory is writable: `chmod -R 755 uploads/`

### Port Already in Use
- Use a different port: `php -S localhost:8080 -t public`

### Session Errors
- Ensure `uploads/` directory exists and is writable
- Check PHP session configuration in `php.ini`

## Next Steps

1. Create an organisation in the database
2. Register a user account
3. Create an employee record for that user
4. View your digital ID card!

