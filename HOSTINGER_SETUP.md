# Quick Setup Guide for Hostinger

## Immediate Steps to Fix 403 Error

### Step 1: Check Document Root

In Hostinger control panel:
1. Go to **Domains** → Your domain
2. Check **Document Root** setting
3. **Option A (Recommended):** Set document root to `public/` folder
4. **Option B:** If you can't change it, document root is project root (use root `.htaccess`)

### Step 2: Upload Files

Upload ALL files maintaining this structure:
```
your-domain/
├── .htaccess          ← Root .htaccess (if doc root is project root)
├── .env               ← Your environment file
├── config/
├── includes/
├── public/
│   ├── .htaccess     ← Public .htaccess (always needed)
│   ├── index.php
│   └── ...
├── shared-auth/
├── src/
└── uploads/
```

### Step 3: Set File Permissions

Via Hostinger File Manager or FTP:
- **Directories:** 755
- **Files:** 644
- **uploads/:** 755 (must be writable)

### Step 4: Create .env File

Create `.env` in project root (NOT in public/):
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

**Important:** Update `APP_URL` to your actual domain URL (with https://)

### Step 5: Set Up Database

1. **In Hostinger phpMyAdmin:**
   - Select your database: `u248320297_digital_ids`
   - Go to **Import** tab
   - Upload and run: `sql/complete_schema.sql`
   - This creates all necessary tables

2. **Or run SQL manually:**
   - Copy contents of `sql/complete_schema.sql`
   - Paste into phpMyAdmin SQL tab
   - Execute

### Step 6: Test Connection

1. Visit: `https://lightslategrey-weasel-963972.hostingersite.com/test-connection.php`
2. This will test your database connection
3. **Delete this file after testing!**

### Step 7: Create Super Admin

After database is set up:

**Option A - Via SSH (if available):**
```bash
php sql/create_super_admin.php
```

**Option B - Via Web:**
1. Visit: `https://lightslategrey-weasel-963972.hostingersite.com/admin/create-super-admin.php`
2. Fill in the form to create super admin

## Common 403 Error Causes

1. **Missing .htaccess** → Ensure `.htaccess` exists in `public/` directory
2. **Wrong Document Root** → Should point to `public/` folder
3. **File Permissions** → Directories 755, files 644
4. **Index File Missing** → Ensure `public/index.php` exists
5. **Apache mod_rewrite** → Should be enabled (contact Hostinger support if not)

## If Still Getting 403

1. **Check Error Logs:**
   - Hostinger control panel → Logs
   - Look for specific error messages

2. **Test Simple PHP:**
   - Create `public/test.php` with: `<?php echo "Hello"; ?>`
   - If this works, issue is with application config
   - If this fails, issue is with server setup

3. **Contact Hostinger Support:**
   - Ask them to check:
     - Document root configuration
     - mod_rewrite enabled
     - PHP version (needs 7.4+)
     - File permissions

## Security Notes

- ✅ `.env` file should be in project root (NOT in public/)
- ✅ `.env` file permissions should be 644 (not 777)
- ✅ Delete `test-connection.php` after testing
- ✅ Ensure `uploads/` is writable but not executable
- ✅ Use HTTPS (update APP_URL to https://)

