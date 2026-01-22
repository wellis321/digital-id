# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Critical Standards

**UK English Only** - ALL code, comments, documentation, and user-facing text must use UK English:
- `organisation` not `organization`
- `synchronisation` not `synchronization`
- `colour`/`centre` in text (CSS properties like `color`/`center` are fine)
- See `DEVELOPMENT_GUIDELINES.md` for complete spelling list

**No Emojis** - Use Font Awesome 6 icons instead (loaded in `includes/header.php`):
```html
<i class="fas fa-lock"></i>      <!-- not 🔒 -->
<i class="fas fa-building"></i>  <!-- not 🏢 -->
```

## Project Overview

Multi-tenant digital ID card system for social care providers. Provides secure, verifiable employee identification with QR codes, NFC, and visual verification.

**Stack:** PHP 8.0+ / MySQL / Server-rendered PHP with PWA support / TypeScript MCP server

## Development Commands

```bash
# Start local server (runs on localhost:8000)
./start.sh
# or: php -S localhost:8000 -t public public/router.php

# Run PHP unit tests
./vendor/bin/phpunit                    # All tests
./vendor/bin/phpunit tests/Unit         # Unit tests only
./vendor/bin/phpunit --filter TestName  # Single test

# Run E2E tests (Playwright)
npm test                    # All browsers
npm run test:headed         # With browser UI
npm run test:ui             # Interactive UI mode
npm run test:debug          # Debug mode

# MCP Server
cd mcp-server && npm install && npm run build
npm start       # Run server
npm run dev     # Watch mode
```

### Database Setup

```bash
mysql -u root -p -e "CREATE DATABASE digital_ids CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p digital_ids < shared-auth/migrations/core_schema.sql
mysql -u root -p digital_ids < sql/schema.sql
```

## Architecture

### Multi-Tenant Isolation

ALL database queries MUST filter by `organisation_id`. The user's organisation is stored in session on login.

```php
// Correct - always filter by organisation
$stmt = $pdo->prepare("SELECT * FROM employees WHERE organisation_id = ? AND id = ?");
$stmt->execute([$organisationId, $employeeId]);

// Wrong - data leak across tenants
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
```

### Authentication Flow

```
Request → config/config.php (bootstrap, session)
        → shared-auth/src/Auth.php (validate session)
        → shared-auth/src/RBAC.php (check permissions)
        → Page logic (filtered by organisation_id)
```

### Key Components

| Location | Purpose |
|----------|---------|
| `config/config.php` | Application bootstrap, constants, autoloader |
| `shared-auth/src/` | Auth.php, RBAC.php, CSRF.php, OrganisationalUnits.php |
| `src/classes/DigitalID.php` | ID card creation, time-limited tokens (5 min default) |
| `src/classes/VerificationService.php` | Token verification, audit logging |
| `src/classes/EntraIntegration.php` | Microsoft Entra/365 SSO |
| `src/classes/StaffServiceClient.php` | External staff API integration |
| `public/verify.php` | Public verification page |

### Database Tables

- `organisations` - Multi-tenant organisations
- `employees` - Employee records (links to `users` and `organisations`)
- `digital_id_cards` - Active ID cards with QR/NFC tokens
- `verification_logs` - Complete audit trail
- `organisational_units` - Hierarchical structure (region → area → service)

### External Integrations

**Microsoft Entra** - OAuth SSO, employee sync from Azure AD. Tracked in `entra_sync` table.

**Staff Service API** - Webhook at `public/api/staff-service-webhook.php`, cron sync via `scripts/sync-staff-service.php`.

## Code Patterns

### RBAC Permission Checks

```php
use SharedAuth\RBAC;

if (!RBAC::hasPermission('manage_employees')) {
    header('Location: /access-denied.php');
    exit;
}
```

### CSRF Protection

```php
use SharedAuth\CSRF;

// In forms
<input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">

// In handlers
if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token');
}
```

### Adding a New Page

```php
<?php
require_once __DIR__ . '/../config/config.php';
use SharedAuth\Auth;
Auth::requireAuth();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!-- Page content -->
<?php include __DIR__ . '/../includes/footer.php'; ?>
```

## Environment Configuration

Copy `.env.example` to `.env` and configure:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` - Database credentials
- `APP_URL` - Application URL
- `ENTRA_CLIENT_SECRET` - Optional Microsoft Entra integration
- `STAFF_SERVICE_URL`, `STAFF_SERVICE_API_KEY` - Optional Staff Service API

The MCP server requires `ORGANISATION_ID` in its `.env` for organisation-scoped access.

## Migration Notes

Schema changes documented in:
- `RUN_EMPLOYEE_MIGRATION.md`
- `RUN_REFERENCE_MIGRATION.md`
- `sql/migrate_organisational_units.sql`
