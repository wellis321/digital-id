# Testing Instructions for Security & Accessibility Fixes

## Automated Test Suite

A comprehensive test suite has been created to verify all fixes. Access it at:

**URL:** `http://localhost:8000/test-security-accessibility.php`

This will automatically test:
- ✅ Security fixes (CSP, rate limiting, path traversal protection)
- ✅ Accessibility fixes (skip links, ARIA labels, form associations)
- ✅ Error pages existence

## Manual Testing Steps

### 1. Rate Limiting Test

**Steps:**
1. Go to login page: `http://localhost:8000/login.php`
2. Try logging in with incorrect credentials **6 times**
3. On the 6th attempt, you should see: "Too many login attempts. Please try again in X minutes."

**Expected Result:** Rate limiting message appears after 5 failed attempts

---

### 2. Skip Link Test (Keyboard Navigation)

**Steps:**
1. Go to any page (e.g., `http://localhost:8000/index.php`)
2. Press **Tab** key immediately on page load
3. A "Skip to main content" link should appear in the top-left corner
4. Press **Enter** to activate it
5. Focus should jump to the main content area

**Expected Result:** Skip link appears on Tab, Enter jumps to main content

---

### 3. Form Error Associations (Screen Reader)

**Steps:**
1. Go to login page: `http://localhost:8000/login.php`
2. Leave email field empty and submit form
3. Open browser DevTools → Elements tab
4. Inspect the email input field
5. Check for these attributes:
   - `aria-invalid="true"` (when error exists)
   - `aria-describedby="email-error"` (linking to error message)
   - `aria-required="true"`

**Expected Result:** Form inputs have proper ARIA attributes linking to error messages

---

### 4. Screen Reader Test

**Steps:**
1. Use NVDA (Windows), JAWS, or VoiceOver (Mac)
2. Navigate to login page
3. Tab through the form
4. Submit with errors
5. Screen reader should announce:
   - Field labels
   - Required status
   - Error messages when they appear
   - Alert messages

**Expected Result:** All interactive elements and errors are announced by screen reader

---

### 5. Error Pages Test

**Steps:**
1. **For PHP Built-in Server:** Make sure you're running with the router: `php -S localhost:8000 -t public public/router.php`
   - Or use `./start.sh` which includes the router
2. Visit a non-existent page: `http://localhost:8000/nonexistent-page.php`
3. Should see custom 404 page with navigation options

**Expected Result:** Custom 404 page displays instead of default server error

**Note:** If using PHP's built-in server, you need the `router.php` file. On Apache, the `.htaccess` ErrorDocument directives handle this automatically.

---

### 6. Content Security Policy Header

**Steps:**
1. Open any page in browser
2. Open DevTools → Network tab
3. Reload page
4. Click on any request (e.g., `index.php`)
5. Check Response Headers
6. Look for: `Content-Security-Policy`

**Expected Result:** CSP header is present in response headers

---

### 7. Path Traversal Protection

**Steps:**
1. Try accessing: `http://localhost:8000/view-image.php?path=../../../config/config.php`
2. Should return 403 Forbidden

**Expected Result:** Path traversal attempts are blocked

---

### 8. Descriptive Alt Text

**Steps:**
1. Go to ID card page: `http://localhost:8000/id-card.php` (while logged in)
2. Right-click on photo → Inspect
3. Check `alt` attribute
4. Should contain descriptive text like "ID card photo for [Name]"

**Expected Result:** Alt text is descriptive, not just "Photo"

---

### 9. Mobile Menu ARIA

**Steps:**
1. Resize browser to mobile width (< 968px)
2. Click hamburger menu
3. Inspect menu toggle button
4. Check for `aria-expanded` attribute (should change from "false" to "true")
5. Check navigation has `role="navigation"` and `aria-label`

**Expected Result:** Mobile menu has proper ARIA attributes

---

### 10. Live Regions for Alerts

**Steps:**
1. Go to login page
2. Submit form with errors
3. Inspect error alert div
4. Should have `role="alert"` and `aria-live="assertive"`

**Expected Result:** Alerts are announced immediately by screen readers

---

## Browser DevTools Quick Checks

### Security Headers Check
1. Open DevTools → Network tab
2. Reload page
3. Select main document request
4. Check Response Headers for:
   - `Content-Security-Policy` ✅
   - `X-Frame-Options: SAMEORIGIN` ✅
   - `X-XSS-Protection: 1; mode=block` ✅
   - `X-Content-Type-Options: nosniff` ✅

### Accessibility Check (Chrome)
1. Open DevTools → Lighthouse tab
2. Select "Accessibility" category
3. Run audit
4. Should score 90+ (some issues may remain for Level AA)

---

## Test Results Summary

After running all tests, you should see:

✅ **Security:**
- CSP header present
- Rate limiting working
- Path traversal blocked
- HTTPS redirect available (uncomment for production)

✅ **Accessibility:**
- Skip link functional
- ARIA labels present
- Form errors associated
- Descriptive alt text
- Live regions working

✅ **Error Pages:**
- 404, 403, 500 pages exist
- ErrorDocument directives configured

---

## Important Notes

⚠️ **Delete test file after testing:**
- Remove `public/test-security-accessibility.php` before production deployment
- This file should not be publicly accessible

⚠️ **HTTPS Redirect:**
- Currently commented in `.htaccess`
- Uncomment when deploying to production with HTTPS enabled

⚠️ **CSP Policy:**
- Currently uses `unsafe-inline` for compatibility
- Consider moving to nonce-based CSP for stricter security in future

---

## Next Steps

1. Run automated test suite: `http://localhost:8000/test-security-accessibility.php`
2. Complete manual tests above
3. Review any warnings or failures
4. Delete test file before production
5. Uncomment HTTPS redirect when deploying

