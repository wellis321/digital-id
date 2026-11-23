# Automated Testing Guide

This guide explains how to convert the manual test cases in `TEST_PLAN.md` into automated tests.

## Testing Strategy

### Test Types

1. **Unit Tests** (PHPUnit)
   - Test individual classes and methods
   - Fast execution, isolated tests
   - Examples: `VerificationService`, `DigitalID`, `Auth`, `Employee`

2. **Integration Tests** (PHPUnit)
   - Test API endpoints and database interactions
   - Test multi-tenant isolation
   - Examples: `/api/verify-token.php`, employee creation

3. **End-to-End Tests** (Playwright)
   - Test full user workflows in browser
   - Test UI interactions and page flows
   - Examples: Login → View ID card → Verify QR code

4. **API Tests** (PHPUnit + HTTP Client)
   - Test JSON API endpoints
   - Test request/response formats
   - Examples: Building access API, verification API

## Setup Instructions

### 1. PHPUnit Setup (Backend Testing)

#### Install PHPUnit

```bash
# Install Composer if not already installed
curl -sS https://getcomposer.org/installer | php

# Install PHPUnit and dependencies
composer require --dev phpunit/phpunit
composer require --dev phpunit/phpunit-selenium
```

#### Create `composer.json` (if it doesn't exist)

```json
{
    "name": "digital-id/app",
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "guzzlehttp/guzzle": "^7.0"
    },
    "autoload": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

#### Create `phpunit.xml` Configuration

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="API">
            <directory>tests/API</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_NAME" value="digital_ids_test"/>
        <env name="DB_HOST" value="localhost"/>
        <env name="DB_USER" value="test_user"/>
        <env name="DB_PASS" value="test_password"/>
    </php>
</phpunit>
```

### 2. Playwright Setup (E2E Testing)

#### Install Playwright

```bash
# Install Node.js dependencies
npm init -y
npm install --save-dev @playwright/test

# Install Playwright browsers
npx playwright install
```

#### Create `playwright.config.js`

```javascript
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: process.env.APP_URL || 'http://localhost:8000',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],
});
```

## Test Structure

```
tests/
├── bootstrap.php              # Test bootstrap (load config, autoloader)
├── Unit/                      # Unit tests
│   ├── DigitalIDTest.php
│   ├── VerificationServiceTest.php
│   ├── AuthTest.php
│   └── EmployeeTest.php
├── Integration/               # Integration tests
│   ├── DatabaseTest.php
│   ├── OrganisationIsolationTest.php
│   └── CheckInServiceTest.php
├── API/                       # API endpoint tests
│   ├── VerifyTokenApiTest.php
│   └── BuildingAccessApiTest.php
└── e2e/                       # End-to-end tests (Playwright)
    ├── auth.spec.js
    ├── id-card.spec.js
    ├── verification.spec.js
    └── check-in.spec.js
```

## Running Tests

### PHPUnit Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite API

# Run specific test file
./vendor/bin/phpunit tests/Unit/DigitalIDTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Playwright Tests

```bash
# Run all E2E tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/id-card.spec.js

# Run in headed mode (see browser)
npx playwright test --headed

# Run with UI mode
npx playwright test --ui

# Generate test report
npx playwright show-report
```

## Mapping Test Plan to Automated Tests

### P0 Tests (Critical) - Priority for Automation

| Test Case | Test Type | File |
|-----------|-----------|------|
| TC-001: View ID card | E2E | `e2e/id-card.spec.js` |
| TC-002: QR code scanning | E2E | `e2e/verification.spec.js` |
| TC-003: Token expiry | Unit + Integration | `Unit/DigitalIDTest.php`, `Integration/TokenExpiryTest.php` |
| TC-009: Public verification | E2E | `e2e/verification.spec.js` |
| TC-010: Manual lookup | E2E + API | `e2e/verification.spec.js`, `API/VerifyTokenApiTest.php` |
| TC-018: Organisation isolation | Integration | `Integration/OrganisationIsolationTest.php` |
| TC-022: Email verification | Integration | `Integration/AuthTest.php` |
| TC-023-025: RBAC | Unit | `Unit/RBACTest.php` |
| TC-026-028: Security (CSRF, XSS, SQL) | Integration | `Integration/SecurityTest.php` |
| TC-029-031: Token security | Unit | `Unit/DigitalIDTest.php` |

### P1 Tests (High Priority)

| Test Case | Test Type | File |
|-----------|-----------|------|
| TC-032-035: Entra SSO | Integration | `Integration/EntraIntegrationTest.php` |
| TC-042-049: Check-in sessions | Integration + E2E | `Integration/CheckInServiceTest.php`, `e2e/check-in.spec.js` |
| TC-053-062: Building access API | API | `API/BuildingAccessApiTest.php` |
| TC-063-068: Staff Service | Integration | `Integration/StaffServiceTest.php` |

### P2 Tests (Medium Priority)

| Test Case | Test Type | File |
|-----------|-----------|------|
| TC-069-079: MCP server | Integration | `Integration/MCPServerTest.php` |
| TC-080-093: Admin features | E2E | `e2e/admin.spec.js` |

## Test Data Management

### Database Setup

Create a separate test database and use migrations to set it up:

```php
// tests/bootstrap.php
require_once __DIR__ . '/../config/config.php';

// Set test database
define('DB_NAME', 'digital_ids_test');

// Run migrations
exec('mysql -u test_user -ptest_password digital_ids_test < sql/schema.sql');
```

### Test Fixtures

Create test data factories:

```php
// tests/Fixtures/EmployeeFactory.php
class EmployeeFactory {
    public static function create($organisationId, $data = []) {
        // Create test employee
    }
}
```

## Continuous Integration

### GitHub Actions Example

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  phpunit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run PHPUnit
        run: ./vendor/bin/phpunit

  playwright:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '18'
      - name: Install dependencies
        run: npm install
      - name: Install Playwright
        run: npx playwright install --with-deps
      - name: Run Playwright
        run: npx playwright test
```

## Next Steps

1. **Start with P0 Tests**: Automate the most critical tests first
2. **Create Test Infrastructure**: Set up PHPUnit and Playwright
3. **Write Example Tests**: See example test files below
4. **Run Tests Regularly**: Integrate into development workflow
5. **Expand Coverage**: Gradually automate more test cases

## Example Test Files

See the following example test files:
- `tests/Unit/DigitalIDTest.php` - Unit test example
- `tests/API/VerifyTokenApiTest.php` - API test example
- `tests/e2e/id-card.spec.js` - E2E test example
- `tests/Integration/OrganisationIsolationTest.php` - Integration test example

