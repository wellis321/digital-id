# Quick Fix for 403 Error on Hostinger

## Most Likely Causes (in order):

### 1. Missing .htaccess File
**Fix:** Upload `public/.htaccess` file to your server
- File is already created in the project
- Upload it to: `public/.htaccess` on your server

### 2. Document Root Not Set Correctly
**Fix:** In Hostinger control panel:
- Go to **Domains** → Your domain
- Set **Document Root** to: `public/` folder
- OR if you can't change it, ensure root `.htaccess` is uploaded

### 3. Database Schema Not Created
**Fix:** Run the database schema:
1. Log into Hostinger phpMyAdmin
2. Select database: `u248320297_digital_ids`
3. Go to **Import** tab
4. Upload file: `sql/complete_schema.sql`
5. Click **Go** to import

### 4. .env File Issues
**Fix:** Ensure `.env` file:
- Is in project root (NOT in public/)
- Has NO leading spaces before keys
- Has correct format:
  ```env
  APP_ENV=production
  APP_URL=https://lightslategrey-weasel-963972.hostingersite.com
  DB_HOST=localhost
  DB_NAME=u248320297_digital_ids
  DB_USER=u248320297_digitalids
  DB_PASS=Rf|SDCD:3l
  DB_CHARSET=utf8mb4
  ```

### 5. File Permissions
**Fix:** Set via Hostinger File Manager:
- All directories: **755**
- All files: **644**
- `uploads/` directory: **755** (must be writable)

## Step-by-Step Fix:

1. **Upload `.htaccess` files:**
   - Upload `public/.htaccess` to `public/` folder on server
   - If document root is project root, also upload root `.htaccess`

2. **Set document root:**
   - Hostinger control panel → Domains → Document Root
   - Set to: `public/` (recommended)

3. **Create database schema:**
   - phpMyAdmin → Select database → Import
   - Upload: `sql/complete_schema.sql`

4. **Fix .env file:**
   - Remove any leading spaces
   - Ensure it's in project root
   - Update `APP_URL` to your actual domain

5. **Test:**
   - Visit: `https://lightslategrey-weasel-963972.hostingersite.com/test-connection.php`
   - Should show database connection status
   - Delete test file after

## If Still Not Working:

Check Hostinger error logs:
- Control panel → Logs → Error Log
- Look for specific PHP errors or permission errors

Contact Hostinger support with:
- Your domain
- Error message from logs
- Request to check: mod_rewrite enabled, PHP version (7.4+), document root setting

