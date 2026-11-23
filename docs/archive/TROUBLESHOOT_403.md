# Troubleshooting 403 Error on Hostinger

## Quick Fix Steps

The 403 error you're seeing could be caused by several things. Try these in order:

### Step 1: Temporarily Disable CSP Header (Most Likely Fix)

The Content Security Policy header we added might be too strict or causing issues with your hosting provider.

**Option A - Edit .htaccess directly on server:**
1. Log into Hostinger File Manager
2. Navigate to `public/.htaccess`
3. Find the line with `Content-Security-Policy`
4. Comment it out by adding `#` at the start:
   ```apache
   # Header always set Content-Security-Policy "..."
   ```

**Option B - Upload backup .htaccess:**
1. Use the file `.htaccess.without-csp` I created
2. Rename it to `.htaccess` and upload to `public/` directory
3. This version has CSP disabled

### Step 2: Check Apache Error Logs

1. Log into Hostinger control panel
2. Go to **Logs** → **Error Log**
3. Look for specific error messages about:
   - `.htaccess` syntax errors
   - `mod_headers` not enabled
   - Permission denied

### Step 3: Verify File Permissions

Ensure these permissions are correct:
- `public/.htaccess` → **644**
- `public/index.php` → **644**
- All directories → **755**

### Step 4: Check if mod_headers is Enabled

Some hosting providers don't have `mod_headers` enabled. The `<IfModule mod_headers.c>` wrapper should prevent errors, but if the entire block fails, try:

1. Remove ALL `Header` directives temporarily
2. See if site works
3. If yes, add headers back one by one to find the problematic one

### Step 5: Test with Minimal .htaccess

Create a minimal `.htaccess` file to test:

```apache
DirectoryIndex index.php index.html
```

If this works, gradually add back security headers.

### Step 6: Check Document Root

Ensure document root is set to `public/` directory:
1. Hostinger control panel → Domains → Your domain
2. Check Document Root setting
3. Should be: `public/` or `public_html/public/` (depending on structure)

### Step 7: Verify index.php Exists

Check that `public/index.php` exists and is readable:
- File should exist at: `public/index.php`
- Permissions should be: **644**

---

## Most Common Causes

1. **CSP Header Too Long/Complex** - Try disabling it first
2. **mod_headers Not Available** - Headers won't work but shouldn't cause 403
3. **.htaccess Syntax Error** - Check error logs
4. **File Permissions** - Ensure correct permissions
5. **Document Root Wrong** - Should point to `public/`

---

## Quick Test

1. **Temporarily rename .htaccess:**
   ```bash
   mv public/.htaccess public/.htaccess.backup
   ```
2. **Visit site** - If it works, the issue is in `.htaccess`
3. **Add back sections one by one** to identify the problem

---

## If Nothing Works

Contact Hostinger support with:
- Your domain URL
- Error message from logs
- Request to verify:
  - `mod_rewrite` is enabled
  - `mod_headers` is enabled (if using security headers)
  - Document root configuration
  - PHP version (needs 7.4+)

