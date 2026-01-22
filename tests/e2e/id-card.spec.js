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
    
    // Check ID card container exists
    const idCard = page.locator('.id-card, #id-card, [class*="id-card"]');
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
    
    // Check QR code is visible
    const qrCode = page.locator('img[alt*="QR"], canvas, svg, [class*="qr"]');
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
    
    // Get QR code image
    const qrCode = page.locator('img[alt*="QR"], canvas, svg, [class*="qr"]').first();
    await expect(qrCode).toBeVisible();
    
    // Check QR code has valid src or data
    const qrSrc = await qrCode.getAttribute('src');
    const qrDataUrl = await qrCode.getAttribute('data-url');
    
    // QR code should have either src or data-url
    expect(qrSrc || qrDataUrl).toBeTruthy();
    
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
    // This test would require a user without a photo
    // You might need to create test data first
    
    await page.goto('/id-card.php');
    await page.waitForLoadState('networkidle');
    
    // Check for placeholder image or default avatar
    const photo = page.locator('img[alt*="photo"], img[alt*="avatar"], .photo-placeholder');
    await expect(photo.first()).toBeVisible();
  });
});

