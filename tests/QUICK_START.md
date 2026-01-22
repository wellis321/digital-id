# Quick Start Guide for Testing

Now that your test database is set up, follow these steps to start running automated tests.

## Step 1: Install Dependencies

### PHPUnit (Backend Tests)

```bash
# Check if composer.json exists, if not we'll create it
composer require --dev phpunit/phpunit:^10.0 guzzlehttp/guzzle:^7.0
```

### Playwright (E2E Tests)

```bash
# Initialize npm if needed
npm init -y

# Install Playwright
npm install --save-dev @playwright/test

# Install Playwright browsers
npx playwright install
```

## Step 2: Update Test Configuration

### Update phpunit.xml

Make sure `phpunit.xml` has the correct database credentials for your test database:

```xml
<env name="DB_NAME" value="digital_ids_test"/>
<env name="DB_HOST" value="localhost:8889"/>
<env name="DB_USER" value="root"/>
<env name="DB_PASS" value=""/>
```

### Update playwright.config.js

The Playwright config should already be set up, but verify the base URL:

```javascript
use: {
  baseURL: process.env.APP_URL || 'http://localhost:8000',
}
```

## Step 3: Create Test Data

Before running tests, you may want to create some test data. You can use the test helpers:

```php
// Example: Create test organisation and users
$orgId = TestHelper::createTestOrganisation('Test Org', 'test.local');
$userId = TestHelper::createTestUser($orgId, 'test@test.local', 'password', 'staff');
$employeeId = TestHelper::createTestEmployee($orgId, $userId, 'EMP001', 'John', 'Doe');
```

Or run a setup script to create initial test data (we can create this if needed).

## Step 4: Run Tests

### Run PHPUnit Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite API

# Run specific test file
./vendor/bin/phpunit tests/Unit/DigitalIDTest.php

# Run with verbose output
./vendor/bin/phpunit --verbose

# Run with coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### Run Playwright E2E Tests

```bash
# Make sure your local server is running first
php -S localhost:8000 -t public

# In another terminal, run Playwright tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/id-card.spec.js

# Run in headed mode (see the browser)
npx playwright test --headed

# Run with UI mode (interactive)
npx playwright test --ui
```

## Step 5: Start with Simple Tests

Begin with the example tests we've created:

1. **Unit Test**: `tests/Unit/DigitalIDTest.php`
   - Tests token generation and validation
   - Fast and isolated

2. **API Test**: `tests/API/VerifyTokenApiTest.php`
   - Tests the verification API endpoint
   - Requires server running

3. **Integration Test**: `tests/Integration/OrganisationIsolationTest.php`
   - Tests multi-tenant isolation
   - Requires database

4. **E2E Test**: `tests/e2e/id-card.spec.js`
   - Tests full user workflow
   - Requires server running and test users

## Step 6: Fix Any Issues

If tests fail:

1. **Check database connection**: Make sure test database is accessible
2. **Check test data**: Some tests require specific test data to exist
3. **Check server**: E2E tests need the application server running
4. **Check dependencies**: Make sure all Composer/npm packages are installed

## Step 7: Expand Test Coverage

Once basic tests are working:

1. Add more test cases from `TEST_PLAN.md`
2. Create test data factories for common scenarios
3. Set up CI/CD integration (GitHub Actions, etc.)
4. Add performance tests
5. Add security tests

## Troubleshooting

### "Class not found" errors
- Run `composer dump-autoload`
- Check that classes are in the correct namespace

### "Database connection failed"
- Verify database credentials in `phpunit.xml`
- Make sure test database exists: `php tests/verify-database.php`

### "Page not found" in E2E tests
- Make sure server is running: `php -S localhost:8000 -t public`
- Check base URL in `playwright.config.js`

### Tests are slow
- Run only specific test suites
- Use `--filter` to run specific tests
- Check database query performance

## Next Steps

1. ✅ Test database created
2. ⬜ Install dependencies
3. ⬜ Run first test
4. ⬜ Fix any issues
5. ⬜ Expand test coverage
6. ⬜ Set up CI/CD

## Helpful Commands

```bash
# Verify database setup
php tests/verify-database.php

# Recreate test database
php tests/setup-test-db-simple.php

# Check PHPUnit version
./vendor/bin/phpunit --version

# Check Playwright version
npx playwright --version

# List all available tests
./vendor/bin/phpunit --list-tests
npx playwright test --list
```


