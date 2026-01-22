# Next Steps for Testing

✅ **Database Setup Complete!** Your test database `digital_ids_test` has 9 tables ready.

## Step 1: Install Testing Dependencies

### Install PHPUnit (for backend/API tests)

```bash
# Install Composer dependencies
composer install
```

This will install:
- PHPUnit 10.0 (testing framework)
- Guzzle HTTP Client (for API testing)

### Install Playwright (for E2E/browser tests)

```bash
# Install Node.js dependencies
npm install

# Install Playwright browsers (this may take a few minutes)
npx playwright install
```

## Step 2: Run Your First Test

### Option A: Run a Unit Test (Fastest)

```bash
# Run the DigitalID unit test
./vendor/bin/phpunit tests/Unit/DigitalIDTest.php
```

This tests token generation and validation - no server needed!

### Option B: Run an API Test

First, make sure your server is running:
```bash
php -S localhost:8000 -t public
```

Then in another terminal:
```bash
./vendor/bin/phpunit tests/API/VerifyTokenApiTest.php
```

### Option C: Run an E2E Test

Make sure your server is running, then:
```bash
npx playwright test tests/e2e/id-card.spec.js
```

## Step 3: Run All Tests

```bash
# All PHPUnit tests
./vendor/bin/phpunit

# All Playwright tests
npx playwright test
```

## Step 4: Check Test Results

- **PHPUnit**: Results shown in terminal, or generate HTML coverage: `./vendor/bin/phpunit --coverage-html coverage/`
- **Playwright**: Results shown in terminal, or view HTML report: `npx playwright show-report`

## Common Issues & Solutions

### "composer: command not found"
Install Composer: https://getcomposer.org/download/

### "npm: command not found"
Install Node.js: https://nodejs.org/

### "Class not found" errors
Run: `composer dump-autoload`

### Tests fail with database errors
Verify database: `php tests/verify-database.php`

### E2E tests fail with "page not found"
Make sure server is running: `php -S localhost:8000 -t public`

## What Tests Are Available?

### Unit Tests (`tests/Unit/`)
- `DigitalIDTest.php` - Token generation, expiry, revocation

### Integration Tests (`tests/Integration/`)
- `OrganisationIsolationTest.php` - Multi-tenant security

### API Tests (`tests/API/`)
- `VerifyTokenApiTest.php` - Verification API endpoint

### E2E Tests (`tests/e2e/`)
- `id-card.spec.js` - ID card display workflow
- `verification.spec.js` - Verification workflow

## Expanding Tests

Once basic tests work, you can:

1. **Add more test cases** from `TEST_PLAN.md`
2. **Create test data** using `TestHelper` class
3. **Add CI/CD** integration (GitHub Actions, etc.)
4. **Generate coverage reports** to see what's tested

## Quick Reference

```bash
# Verify database
php tests/verify-database.php

# Recreate database
php tests/setup-test-db-simple.php

# Run all PHPUnit tests
./vendor/bin/phpunit

# Run all Playwright tests
npx playwright test

# Run specific test
./vendor/bin/phpunit tests/Unit/DigitalIDTest.php
npx playwright test tests/e2e/id-card.spec.js

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/

# View Playwright report
npx playwright show-report
```

## Ready to Start?

1. Run: `composer install`
2. Run: `npm install && npx playwright install`
3. Run: `./vendor/bin/phpunit tests/Unit/DigitalIDTest.php`

If that works, you're ready to expand your test suite! 🎉


