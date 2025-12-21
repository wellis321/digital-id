# Shared Authentication Package

A reusable authentication package for multi-tenant PHP applications with organization-based access control.

## Features

- User authentication (login, registration, email verification)
- Role-based access control (RBAC)
- CSRF protection
- Email utilities
- Multi-tenant organization support
- Domain-based organization association
- Flexible organisational hierarchy system (regions, areas, teams, etc.)
- Unit-level admin roles
- CSV/JSON import for organisational structure

## Installation

### As Composer Package

```bash
composer require digital-id/shared-auth
```

### As Git Submodule

```bash
git submodule add https://github.com/your-org/shared-auth.git shared-auth
```

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- PDO extension
- Session support

## Database Setup

Run the migration script to create the core tables:

```sql
source migrations/core_schema.sql
```

## Configuration

The package requires the following constants and functions to be defined in your application:

### Required Constants

- `DB_HOST` - Database host
- `DB_NAME` - Database name
- `DB_USER` - Database user
- `DB_PASS` - Database password
- `DB_CHARSET` - Database charset (default: utf8mb4)
- `APP_NAME` - Application name
- `APP_URL` - Application base URL
- `CSRF_TOKEN_NAME` - CSRF token name (default: 'csrf_token')
- `PASSWORD_MIN_LENGTH` - Minimum password length
- `PASSWORD_REQUIRE_UPPERCASE` - Require uppercase letters
- `PASSWORD_REQUIRE_LOWERCASE` - Require lowercase letters
- `PASSWORD_REQUIRE_NUMBER` - Require numbers
- `PASSWORD_REQUIRE_SPECIAL` - Require special characters
- `VERIFICATION_TOKEN_EXPIRY_HOURS` - Email verification token expiry

### Required Functions

- `getDbConnection()` - Returns a PDO database connection
- `url($path)` - Generates application URLs (optional, has fallback)

## Usage

### Include the Package

```php
require_once 'shared-auth/src/Auth.php';
require_once 'shared-auth/src/RBAC.php';
require_once 'shared-auth/src/CSRF.php';
require_once 'shared-auth/src/Email.php';
require_once 'shared-auth/src/Database.php';
```

### Authentication

```php
// Login
$result = Auth::login($email, $password);
if ($result === true) {
    // Success
} elseif (isset($result['error'])) {
    // Handle error
}

// Check if logged in
if (Auth::isLoggedIn()) {
    $userId = Auth::getUserId();
    $orgId = Auth::getOrganisationId();
}

// Logout
Auth::logout();
```

### Role-Based Access Control

```php
// Check roles
if (RBAC::isAdmin()) {
    // User is admin
}

if (RBAC::hasRole('staff')) {
    // User has staff role
}

// Require specific role
RBAC::requireAdmin();
RBAC::requireRole('staff');
```

### CSRF Protection

```php
// Generate token for form
echo CSRF::tokenField();

// Validate POST request
if (CSRF::validatePost()) {
    // Safe to process
}
```

### Organisational Units

The package includes a flexible organisational hierarchy system that allows organisations to define their own structure (regions, areas, teams, services, etc.).

#### Setup Organisational Structure

```php
require_once 'shared-auth/src/OrganisationalUnits.php';

// Create unit types (e.g., Region, Area, Team)
$result = OrganisationalUnits::createUnitType(
    $organisationId,
    'region',        // Internal name
    'Region',        // Display name
    1,               // Level order (1 = top level)
    null             // Parent type (null for top level)
);

// Create organisational units
$result = OrganisationalUnits::createUnit(
    $organisationId,
    $unitTypeId,
    'North Region',
    'NORTH',         // Optional code
    null,            // Parent unit ID
    $managerUserId   // Optional manager
);

// Assign user to unit
$result = OrganisationalUnits::assignUserToUnit(
    $userId,
    $unitId,
    'member',        // Role in unit
    true             // Is primary unit
);

// Create unit admin role
$result = OrganisationalUnits::createUnitAdminRole(
    $organisationId,
    'region_admin',
    'Region Administrator',
    $regionUnitTypeId,
    true,   // Can manage children
    true,   // Can manage users
    false   // Can manage units
);

// Assign unit admin
$result = OrganisationalUnits::assignUnitAdmin(
    $userId,
    $unitId,
    $unitAdminRoleId
);
```

#### Import from CSV

```php
// CSV format: unit_type,unit_name,unit_code,parent_code,user_email,user_role,is_admin,is_primary
$csvContent = file_get_contents('structure.csv');
$result = OrganisationalUnits::importFromCsv($organisationId, $csvContent);
```

#### Import from JSON

```php
$structure = [
    'unit_types' => [
        ['name' => 'region', 'display_name' => 'Region', 'level_order' => 1],
        ['name' => 'area', 'display_name' => 'Area', 'level_order' => 2, 'parent_type' => 'region'],
        ['name' => 'team', 'display_name' => 'Team', 'level_order' => 3, 'parent_type' => 'area']
    ],
    'units' => [
        ['id' => 'r1', 'unit_type' => 'region', 'name' => 'North Region', 'code' => 'NORTH'],
        ['id' => 'a1', 'unit_type' => 'area', 'name' => 'Area 1', 'code' => 'A1', 'parent_unit_id' => 'r1']
    ],
    'user_assignments' => [
        ['user_email' => 'user@example.com', 'unit_code' => 'A1', 'role_in_unit' => 'member', 'is_primary' => true]
    ]
];

$result = OrganisationalUnits::importFromJson($organisationId, $structure);
```

## License

MIT

