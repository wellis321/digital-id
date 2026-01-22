# Test Suite

This directory contains automated tests for the Digital ID application, based on the test cases defined in `TEST_PLAN.md`.

## Structure

```
tests/
├── bootstrap.php              # Test bootstrap (loads config, autoloader)
├── helpers/                    # Test helper functions
│   ├── TestHelper.php         # Common test utilities
│   └── DatabaseHelper.php     # Database test utilities
├── Unit/                       # Unit tests (fast, isolated)
│   └── DigitalIDTest.php      # Example unit test
├── Integration/                # Integration tests (database, APIs)
│   └── OrganisationIsolationTest.php
├── API/                        # API endpoint tests
│   └── VerifyTokenApiTest.php
└── e2e/                        # End-to-end tests (Playwright)
    ├── id-card.spec.js
    └── verification.spec.js
```

## Setup

### PHPUnit (Backend Tests)

1. Install dependencies:
```bash
composer install
```

2. Create test database:
```bash
mysql -u root -p -e "CREATE DATABASE digital_ids_test;"
mysql -u root -p digital_ids_test < sql/schema.sql
```

3. Update `phpunit.xml` with your database credentials

4. Run tests:
```bash
./vendor/bin/phpunit
```

### Playwright (E2E Tests)

1. Install dependencies:
```bash
npm install
npx playwright install
```

2. Start your local server:
```bash
php -S localhost:8000 -t public
```

3. Run tests:
```bash
npx playwright test
```

## Test Coverage

### Unit Tests
- `DigitalID` class (token generation, validation, expiry)
- `VerificationService` class
- `Auth` class
- `Employee` model

### Integration Tests
- Organisation isolation
- Database queries
- Multi-tenant security
- Check-in sessions

### API Tests
- `/api/verify-token.php` endpoint
- Building access API
- Request/response formats
- Error handling

### E2E Tests
- User login flow
- ID card display
- QR code verification
- Check-in workflows
- Admin features

## Mapping to TEST_PLAN.md

See `TESTING_AUTOMATION_GUIDE.md` for a complete mapping of test cases from `TEST_PLAN.md` to automated tests.

## Running Specific Tests

### PHPUnit
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit --testsuite API

# Run specific test file
./vendor/bin/phpunit tests/Unit/DigitalIDTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Playwright
```bash
# Run all E2E tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/id-card.spec.js

# Run in headed mode (see browser)
npx playwright test --headed

# Run with UI mode
npx playwright test --ui
```

## Test Data

Tests use a separate test database (`digital_ids_test`) to avoid affecting production data.

Test helpers in `tests/helpers/TestHelper.php` provide utilities for:
- Creating test organisations
- Creating test users
- Creating test employees
- Cleaning up test data

## Continuous Integration

Tests can be run in CI/CD pipelines. See `TESTING_AUTOMATION_GUIDE.md` for GitHub Actions examples.

## Contributing

When adding new features:
1. Add test cases to `TEST_PLAN.md`
2. Create corresponding automated tests
3. Ensure tests pass before merging

