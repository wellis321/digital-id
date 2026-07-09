/**
 * End-to-End Tests for ID Card Display
 * Tests: TC-001, TC-002, TC-004
 */

const { test, expect } = require('@playwright/test');

test.describe('ID Card Display', () => {
  
  test.beforeEach(async ({ page }) => {
    // Login as test user
    await page.goto('/login.php');
    await page.fill('input[name="email"]', 'staff1@orga.test');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    
    // Wait for redirect after login
    await page.waitForURL(/\/(index|id-card)\.php/);
  });
  
  /**
   * TC-001: View ID card as logged-in user
   */
  test('should display ID card with all elements', async ({ page }) => {
    await page.goto('/id-card.php');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check ID card container exists. Note: the container and every nested
    // section (header/photo/details/qr) all share "id-card" in their class
    // names, so a broad [class*="id-card"] selector matches multiple
    // elements and trips Playwright's strict mode - target the unique id
    // on the outer container instead.
    const idCard = page.locator('#id-card-content');
    await expect(idCard).toBeVisible();

    // Check employee name is displayed
    const name = page.locator('text=/John|Smith|Employee Name/i');
    await expect(name.first()).toBeVisible();

    // Check employee reference is displayed
    const reference = page.locator('text=/EMP001|Employee Reference/i');
    await expect(reference.first()).toBeVisible();

    // Check organisation name is displayed
    const orgName = page.locator('text=/Test Organisation|Organisation/i');
    await expect(orgName.first()).toBeVisible();

    // Check QR code is visible (the app renders a plain <img alt="QR code...">
    // inside a .id-card-qr wrapper div - matching on the wrapper's class too
    // would pick the div itself first in DOM order, not the image)
    const qrCode = page.locator('img[alt*="QR"]');
    await expect(qrCode.first()).toBeVisible();
    
    // Check no JavaScript errors
    const errors = [];
    page.on('pageerror', error => errors.push(error));
    await page.waitForTimeout(1000);
    expect(errors.length).toBe(0);
  });
  
  /**
   * TC-002: QR code displays and is scannable
   */
  test('should display scannable QR code', async ({ page }) => {
    await page.goto('/id-card.php');
    await page.waitForLoadState('networkidle');
    
    // Get QR code image (see note above on why this doesn't also match [class*="qr"])
    const qrCode = page.locator('img[alt*="QR"]').first();
    await expect(qrCode).toBeVisible();

    // QR image is generated via an external QR service URL (src attribute
    // always set once rendered) - confirm it's actually present
    const qrSrc = await qrCode.getAttribute('src');
    expect(qrSrc).toBeTruthy();
    
    // Check QR code links to verification page
    // This might be in a link or the QR code itself
    const qrLink = page.locator('a[href*="verify"], [data-verify-url]').first();
    if (await qrLink.count() > 0) {
      const href = await qrLink.getAttribute('href');
      expect(href).toContain('verify.php');
      expect(href).toContain('token=');
    }
  });
  
  /**
   * TC-004: ID card works offline (PWA)
   *
   * This is a genuine product/documentation mismatch, not a flaky test -
   * left failing intentionally rather than papered over:
   *
   * docs.php's PWA section tells users "you can view your cached ID card
   * even without an internet connection." But service-worker.js explicitly
   * refuses to cache any .php page ("CRITICAL: Never cache PHP pages - they
   * contain authentication state"), so id-card.php itself is never cached
   * and this reload fails with net::ERR_FAILED while offline on every
   * browser (verified on chromium, not WebKit-specific).
   *
   * Fixing this for real needs a product decision: either the docs are
   * overpromising and should be corrected, or offline viewing needs a
   * different mechanism (e.g. caching a JSON snapshot of the card data via
   * a dedicated endpoint, separate from the authenticated HTML page).
   */
  test('should work offline after initial load', async ({ page, context }) => {
    await page.goto('/id-card.php');
    await page.waitForLoadState('networkidle');
    
    // Check service worker is registered
    const swRegistered = await page.evaluate(() => {
      return 'serviceWorker' in navigator;
    });
    expect(swRegistered).toBe(true);
    
    // Go offline
    await context.setOffline(true);
    
    // Reload page
    await page.reload();
    
    // ID card should still be visible (cached)
    const idCard = page.locator('.id-card, #id-card, [class*="id-card"]');
    await expect(idCard).toBeVisible({ timeout: 5000 });
    
    // Go back online
    await context.setOffline(false);
  });
  
  /**
   * Test ID card page loads quickly
   */
  test('should load page in under 2 seconds', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('/id-card.php');
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;
    
    expect(loadTime).toBeLessThan(2000);
  });
  
  /**
   * Test ID card displays for user without photo
   */
  test('should display placeholder for missing photo', async ({ page }) => {
    // The seeded test employee (EMP001) has no photo_path, so id-card.php
    // renders its "No Photo" placeholder branch - a <div class="id-card-photo">,
    // not an <img>. Both the real photo and the placeholder use the same
    // .id-card-photo class (id-card.php picks one or the other), so confirm
    // it's specifically the placeholder by checking its text.
    await page.goto('/id-card.php');
    await page.waitForLoadState('networkidle');

    const photoElement = page.locator('.id-card-photo').first();
    await expect(photoElement).toBeVisible();
    await expect(photoElement).toContainText('No Photo');
  });
});

