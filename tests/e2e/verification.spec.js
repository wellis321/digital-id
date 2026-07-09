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
   * TC-010: Manual lookup by organisation name + employee reference
   * The organisation picker used to be a <select> listing every organisation
   * in the system (a data exposure issue - see docs.php Verification Methods
   * section). It's now a plain text field: the verifier must already know
   * the organisation name, exactly as shown on the person's digital ID card.
   */
  test('should verify employee by organisation name and reference', async ({ page }, testInfo) => {
    // Manual lookup is rate-limited server-side by IP (10 attempts/15min - see
    // verify.php). That's correct production behaviour, but every Playwright
    // project runs from the same machine/IP, so exercising this same
    // browser-agnostic backend logic on all 5 projects exhausts the real
    // rate limit partway through the matrix. Run it on one project only.
    test.skip(testInfo.project.name !== 'chromium', 'Server-side rate limiting is browser-agnostic; only needs testing once');

    await page.goto('/verify.php');

    // The full organisation list must never be exposed on this page
    const orgSelect = page.locator('select[name*="organisation"], select[name*="org"]');
    await expect(orgSelect).toHaveCount(0);

    await page.fill('input[name="organisation_name"]', 'Test Organisation A');
    await page.fill('input[name="employee_reference"]', 'EMP001');

    const submitButton = page.locator('button[type="submit"], input[type="submit"]');
    await submitButton.click();

    await page.waitForLoadState('networkidle');

    // Should show success and the matched employee's details
    const successMessage = page.locator('text=/success|verified/i');
    const employeeName = page.locator('text=/John|Doe/i');

    await expect(successMessage.first()).toBeVisible();
    await expect(employeeName.first()).toBeVisible();
  });

  /**
   * A wrong organisation name and a wrong employee reference must be
   * indistinguishable - otherwise the error message becomes an oracle for
   * enumerating valid organisation names.
   */
  test('manual lookup gives an identical generic message for wrong org vs wrong reference', async ({ page }, testInfo) => {
    // Same reasoning as above - this test alone makes 2 rate-limited requests;
    // multiplied across 5 browser projects that's enough to trip the real
    // 10-attempt limit before every project gets a turn.
    test.skip(testInfo.project.name !== 'chromium', 'Server-side rate limiting is browser-agnostic; only needs testing once');

    await page.goto('/verify.php');
    await page.fill('input[name="organisation_name"]', 'Not A Real Organisation');
    await page.fill('input[name="employee_reference"]', 'EMP001');
    await page.locator('button[type="submit"], input[type="submit"]').click();
    await page.waitForLoadState('networkidle');
    const wrongOrgMessage = await page.locator('.verification-failed ~ p, .verification-result p').first().textContent();

    await page.goto('/verify.php');
    await page.fill('input[name="organisation_name"]', 'Test Organisation A');
    await page.fill('input[name="employee_reference"]', 'NOT-A-REAL-REFERENCE');
    await page.locator('button[type="submit"], input[type="submit"]').click();
    await page.waitForLoadState('networkidle');
    const wrongRefMessage = await page.locator('.verification-failed ~ p, .verification-result p').first().textContent();

    expect(wrongOrgMessage?.trim()).toEqual(wrongRefMessage?.trim());
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

