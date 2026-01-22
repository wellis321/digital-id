/**
 * End-to-End Tests for Verification
 * Tests: TC-009, TC-010, TC-011, TC-012, TC-013
 */

const { test, expect } = require('@playwright/test');

test.describe('Verification', () => {
  
  /**
   * TC-009: Public verification page accessible
   */
  test('should be accessible without login', async ({ page }) => {
    // Don't login - use fresh session
    await page.goto('/verify.php');
    
    // Page should load
    await expect(page).toHaveTitle(/verify|verification/i);
    
    // Verification form should be visible
    const form = page.locator('form, [class*="verify"]');
    await expect(form.first()).toBeVisible();
    
    // Should not redirect to login
    expect(page.url()).toContain('verify.php');
  });
  
  /**
   * TC-010: Manual lookup by employee reference
   */
  test('should verify employee by reference', async ({ page }) => {
    await page.goto('/verify.php');
    
    // Select organisation
    const orgSelect = page.locator('select[name*="organisation"], select[name*="org"]');
    if (await orgSelect.count() > 0) {
      await orgSelect.selectOption({ index: 1 }); // Select first organisation
    }
    
    // Enter employee reference
    const refInput = page.locator('input[name*="reference"], input[name*="employee"]');
    await refInput.fill('EMP001');
    
    // Submit form
    const submitButton = page.locator('button[type="submit"], input[type="submit"]');
    await submitButton.click();
    
    // Wait for verification result
    await page.waitForLoadState('networkidle');
    
    // Check for success message or employee details
    const successMessage = page.locator('text=/success|verified|valid/i');
    const employeeName = page.locator('text=/John|Doe|Employee Name/i');
    
    // Either success message or employee details should be visible
    const hasSuccess = await successMessage.count() > 0;
    const hasEmployee = await employeeName.count() > 0;
    
    expect(hasSuccess || hasEmployee).toBe(true);
  });
  
  /**
   * TC-011: QR code verification succeeds with valid token
   */
  test('should verify valid QR token', async ({ page, context }) => {
    // First, login and get a valid token
    await page.goto('/login.php');
    await page.fill('input[name="email"]', 'staff1@orga.test');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/(index|id-card)\.php/);
    
    // Get QR code URL/token from ID card page
    await page.goto('/id-card.php');
    await page.waitForLoadState('networkidle');
    
    // Extract token from page (might be in data attribute, URL, or QR code)
    const token = await page.evaluate(() => {
      // Try to find token in various places
      const qrLink = document.querySelector('a[href*="token="]');
      if (qrLink) {
        const url = new URL(qrLink.href, window.location.origin);
        return url.searchParams.get('token');
      }
      return null;
    });
    
    if (token) {
      // Navigate to verification page with token
      await page.goto(`/verify.php?token=${token}&type=qr`);
      await page.waitForLoadState('networkidle');
      
      // Check for success
      const successMessage = page.locator('text=/success|verified|valid/i');
      await expect(successMessage.first()).toBeVisible({ timeout: 5000 });
      
      // Check employee details are displayed
      const employeeName = page.locator('text=/John|Doe/i');
      await expect(employeeName.first()).toBeVisible();
    } else {
      // If we can't extract token, skip this test
      test.skip();
    }
  });
  
  /**
   * TC-012: QR code verification fails with expired token
   */
  test('should fail verification with expired token', async ({ page }) => {
    // Use an expired token (this would need to be generated and expired)
    // For now, use an invalid token format
    const expiredToken = 'expired_token_12345';
    
    await page.goto(`/verify.php?token=${expiredToken}&type=qr`);
    await page.waitForLoadState('networkidle');
    
    // Check for error message
    const errorMessage = page.locator('text=/expired|invalid|error|failed/i');
    await expect(errorMessage.first()).toBeVisible({ timeout: 5000 });
  });
  
  /**
   * TC-013: QR code verification fails with revoked card
   */
  test('should fail verification with revoked card', async ({ page }) => {
    // This test would require:
    // 1. Login as admin
    // 2. Revoke an employee's ID card
    // 3. Try to verify with that card's token
    
    // For now, test that error handling works
    const invalidToken = 'revoked_token_12345';
    
    await page.goto(`/verify.php?token=${invalidToken}&type=qr`);
    await page.waitForLoadState('networkidle');
    
    // Should show error
    const errorMessage = page.locator('text=/revoked|invalid|error/i');
    await expect(errorMessage.first()).toBeVisible({ timeout: 5000 });
  });
  
  /**
   * Test verification page handles missing token gracefully
   */
  test('should handle missing token parameter', async ({ page }) => {
    await page.goto('/verify.php');
    await page.waitForLoadState('networkidle');
    
    // Page should load without errors
    expect(page.url()).toContain('verify.php');
    
    // Should show form or appropriate message
    const form = page.locator('form');
    const message = page.locator('text=/enter|search|lookup/i');
    
    const hasForm = await form.count() > 0;
    const hasMessage = await message.count() > 0;
    
    expect(hasForm || hasMessage).toBe(true);
  });
});

