# Security Remediation Plan

This document outlines security issues identified in the Digital ID application and tracks their remediation status.

**Created:** 2026-01-22
**Status:** COMPLETED

---

## Critical Issues

### 1. Missing Multi-Tenant Isolation in Employee Model
- **File:** `src/models/Employee.php`
- **Risk:** Cross-tenant data access
- **Methods affected:**
  - `findById()` - line 124
  - `findByUserId()` - line 151
- **Fix:** Added optional `$organisationId` parameter to filter queries
- **Status:** [x] Completed

### 2. Missing Multi-Tenant Isolation in DigitalID Class
- **File:** `src/classes/DigitalID.php`
- **Risk:** Cross-tenant token/card access
- **Methods affected:**
  - `findById()` - line 93
  - `findByQrToken()` - line 111
  - `findByNfcToken()` - line 129
- **Fix:** Added `e.organisation_id` to SELECT queries; added optional filtering to `findById()`
- **Status:** [x] Completed

### 3. Debug Logging in Auth.php
- **File:** `shared-auth/src/Auth.php`
- **Risk:** Sensitive data written to debug logs in production
- **Lines:** ~401-567 (multiple occurrences)
- **Fix:** Removed all `#region agent log` blocks
- **Status:** [x] Completed

### 4. API Key Validation Logic Error
- **File:** `public/api/verify-token.php`
- **Line:** 37
- **Risk:** API key requirement bypassed if no key provided
- **Fix:** Now checks if API key is required first, then validates; rejects if missing
- **Status:** [x] Completed

### 5. Dangerous Session Preservation on DB Errors
- **File:** `shared-auth/src/Auth.php`
- **Lines:** 80-86, 96-103
- **Risk:** Allows access with deleted/disabled accounts during DB issues
- **Fix:** Implemented "fail closed" - deny access on DB errors rather than assuming valid
- **Status:** [x] Completed

---

## Medium Issues

### 6. Overly Permissive CORS Header
- **File:** `public/api/verify-token.php`
- **Line:** 19
- **Risk:** Token theft from malicious sites
- **Fix:** CORS now disabled by default; configurable via `VERIFICATION_CORS_ORIGIN` env var
- **Status:** [x] Completed

### 7. Webhook Accepts Unsigned Requests
- **File:** `public/api/staff-service-webhook.php`
- **Line:** 41
- **Risk:** Spoofed webhook events if secret not configured
- **Fix:** Rejects unsigned requests by default; `ALLOW_UNSIGNED_WEBHOOKS=1` for development
- **Status:** [x] Completed

### 8. Exception Messages Exposed to Users
- **File:** `src/models/Employee.php`
- **Lines:** 113, 308, 310
- **Risk:** Information disclosure
- **Fix:** Log detailed errors; return generic message to user
- **Status:** [x] Completed

---

## Minor Issues

### 9. Emoji Usage in verify.php and id-card.php
- **Files:** `public/verify.php`, `public/id-card.php`
- **Lines:** 42, 105 (verify.php); 135, 155 (id-card.php)
- **Risk:** Violates codebase standards
- **Fix:** Replaced with Font Awesome icons
- **Status:** [x] Completed

---

## Summary of Changes

| File | Changes Made |
|------|--------------|
| `src/models/Employee.php` | Added org filter to `findById()`, fixed exception messages |
| `src/classes/DigitalID.php` | Added `organisation_id` to queries, added org filter to `findById()` |
| `src/classes/VerificationService.php` | Updated to pass org_id to Employee::findById() |
| `shared-auth/src/Auth.php` | Removed debug logging, fixed fail-closed on DB errors |
| `public/api/verify-token.php` | Fixed API key validation, configurable CORS |
| `public/api/staff-service-webhook.php` | Require webhook signature by default |
| `public/admin/employees-edit.php` | Updated to use org filter |
| `public/id-card.php` | Added org filter, replaced emojis |
| `public/upload-photo.php` | Updated to use org filter |
| `public/verify.php` | Replaced emojis with Font Awesome |

---

## New Environment Variables

| Variable | Purpose | Default |
|----------|---------|---------|
| `VERIFICATION_CORS_ORIGIN` | Allowed CORS origin for token verification API | Empty (CORS disabled) |
| `ALLOW_UNSIGNED_WEBHOOKS` | Allow unsigned webhook requests (dev only) | Not set (signatures required) |

---

## Testing Checklist

After deployment, verify:
- [x] Multi-tenant isolation: Users cannot access other organisation's data
- [x] API key validation: Required when `REQUIRE_API_KEY_FOR_VERIFICATION=1`
- [x] CORS: Only enabled when `VERIFICATION_CORS_ORIGIN` is set
- [x] Webhook security: Requires signature unless `ALLOW_UNSIGNED_WEBHOOKS=1`
- [x] Error messages: No internal details exposed to users
- [x] Session security: Users logged out on DB errors (fail closed)
