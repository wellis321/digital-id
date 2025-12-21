# Deployment Guide for Hostinger

## Prerequisites

1. **Database Setup** - Create database and user in Hostinger control panel
2. **File Upload** - Upload all project files to your hosting account
3. **Environment Configuration** - Set up `.env` file with production settings

## Step 1: Database Setup

1. **Create Database in Hostinger:**
   - Log into Hostinger control panel
   - Go to "Databases" → "MySQL Databases"
   - Create a new database (e.g., `u248320297_digital_ids`)
   - Create a database user with full privileges
   - Note down the database name, username, and password

2. **Run Database Schema:**
   - Use Hostinger's phpMyAdmin or MySQL command line
   - Import the complete schema:
     ```sql
     -- Run this file: sql/complete_schema.sql
     ```
   - Or run migrations in order:
     ```sql
     -- First: shared-auth/migrations/core_schema.sql
     -- Then: sql/schema.sql
     ```

## Step 2: File Upload

1. **Upload Project Files:**
   - Upload all files to your hosting account
   - Ensure the directory structure is maintained:
     ```
     your-domain/
     ├── config/
     ├── includes/
     ├── public/          ← This should be your document root
     ├── shared-auth/
     ├── src/
     ├── uploads/
     └── .env
     ```

2. **Set Document Root:**
   - In Hostinger control panel, set document root to `public/` directory
   - OR if you can't change document root, you'll need to adjust paths (see below)

## Step 3: Environment Configuration

1. **Create `.env` file in project root:**
   ```env
   APP_ENV=production
   APP_NAME=Digital ID
   APP_URL=https://lightslategrey-weasel-963972.hostingersite.com
   
   DB_HOST=localhost
   DB_NAME=u248320297_digital_ids
   DB_USER=u248320297_digitalids
   DB_PASS=Rf|SDCD:3l
   DB_CHARSET=utf8mb4
   
   MAIL_FROM=digital-ids@outlook.com
   MAIL_REPLY_TO=digital-ids@outlook.com
   ```

2. **Important:** Update `APP_URL` to your actual domain URL

## Step 4: File Permissions

Set correct file permissions via FTP or File Manager:

```bash
# Directories should be 755
chmod 755 config/
chmod 755 includes/
chmod 755 public/
chmod 755 shared-auth/
chmod 755 src/
chmod 755 uploads/

# Files should be 644
chmod 644 .env
chmod 644 config/*.php
chmod 644 public/*.php

# Uploads directory needs write permissions
chmod 755 uploads/
chmod 755 uploads/employees/
chmod 755 uploads/employees/pending/
```

## Step 5: Document Root Configuration

### Option A: Document Root Points to `public/` (Recommended)

If you can set document root to `public/`:
- No changes needed - the application will work as-is
- Access site at: `https://your-domain.com/`

### Option B: Document Root Points to Project Root

If document root is the project root, you need to:

1. **Create `.htaccess` in project root:**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_URI} !^/public/
   RewriteRule ^(.*)$ /public/$1 [L]
   ```

2. **Update paths in `config/config.php`** - The `getBaseUrl()` function should handle this automatically

## Step 6: Verify Installation

1. **Test Database Connection:**
   - Create a test file: `public/test-connection.php`
   ```php
   <?php
   require_once dirname(__DIR__) . '/config/config.php';
   $db = getDbConnection();
   echo "Database connection successful!";
   ?>
   ```
   - Access: `https://your-domain.com/test-connection.php`
   - Delete this file after testing

2. **Check File Permissions:**
   - Ensure `uploads/` directory is writable
   - Check that `.env` file is readable (but not publicly accessible)

3. **Test Application:**
   - Visit: `https://your-domain.com/`
   - Should see the homepage
   - Try logging in or registering

## Troubleshooting 403 Errors

### Common Causes:

1. **Missing `.htaccess` file:**
   - Ensure `.htaccess` exists in `public/` directory
   - Check file permissions (should be 644)

2. **Incorrect Document Root:**
   - Document root should point to `public/` directory
   - Check in Hostinger control panel → Domains → Document Root

3. **Directory Permissions:**
   - Parent directories need execute permission (755)
   - Files need read permission (644)

4. **Apache Configuration:**
   - Ensure `mod_rewrite` is enabled
   - Check that `.htaccess` files are allowed

5. **Index File Missing:**
   - Ensure `public/index.php` exists
   - Check file permissions

### Debug Steps:

1. **Check Error Logs:**
   - Hostinger control panel → Logs
   - Look for specific error messages

2. **Test with Simple File:**
   - Create `public/test.php` with `<?php phpinfo(); ?>`
   - Access it directly
   - If this works, the issue is with the application, not server config

3. **Check PHP Version:**
   - Application requires PHP 7.4+
   - Check in Hostinger control panel → PHP Version

4. **Verify Database Connection:**
   - Check `.env` file exists and is readable
   - Verify database credentials are correct
   - Test connection separately

## Security Checklist

- [ ] `.env` file is in project root (not in `public/`)
- [ ] `.env` file has correct permissions (644, not 777)
- [ ] `uploads/` directory is writable but not executable
- [ ] Database password is strong
- [ ] `APP_URL` is set to HTTPS URL
- [ ] Error display is disabled in production (`APP_ENV=production`)
- [ ] `.htaccess` files are in place

## Post-Deployment

1. **Create Super Admin:**
   - Run: `php sql/create_super_admin.php` via SSH
   - OR use the web interface: `/admin/create-super-admin.php`

2. **Test All Features:**
   - User registration
   - Email verification
   - Login/logout
   - ID card generation
   - Photo upload
   - Admin functions

3. **Set Up Email:**
   - Configure SMTP settings if needed
   - Test email sending

4. **Monitor Logs:**
   - Check error logs regularly
   - Monitor for any issues

