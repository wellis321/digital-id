# Staff Service Integration

The Digital ID application can integrate with the Staff Service to use it as the **sole source of truth** for staff data. When enabled, Digital ID will automatically sync staff information, photos, and signatures from Staff Service.

## Overview

When Staff Service integration is enabled:
- Staff data (name, employee reference, photo, status) is synced from Staff Service
- Staff signatures from Staff Service are displayed on digital ID cards
- Real-time updates are received via webhooks
- Digital ID employees are linked to Staff Service people records

## Configuration

### Step 1: Create API Key in Staff Service

Before configuring Digital ID, you need to create an API key in Staff Service. There are two methods:

#### Method A: Web Interface (Recommended)

1. **Log in to Staff Service** as an organisation administrator
2. **Navigate to Admin** → **API Keys** (in the Admin dropdown menu)
3. **Click "Create API Key"**
4. **Enter a descriptive name** (e.g., "Digital ID Integration")
5. **Click "Create API Key"**
6. **Copy the API key immediately** - it will only be shown once!
   - The key will be displayed in a yellow warning box
   - Use the "Copy" button or manually select and copy the key
   - Store it securely - you won't be able to see it again

#### Method B: Command Line (Alternative)

If you prefer using the command line or need to automate key creation:

1. **Find Your User ID and Organisation ID**:
   - Log in to Staff Service
   - Check the URL when viewing your profile (e.g., `profile.php?id=1` shows user ID 1)
   - Or check the database: `SELECT id, organisation_id, email FROM users WHERE email = 'your-email@example.com';`

2. **Create the API Key**:
   ```bash
   # Navigate to Staff Service directory
   cd /path/to/people-management-service
   
   # Run the create API key script
   # Replace <user_id> and <organisation_id> with your actual IDs
   php scripts/create-api-key.php <user_id> <organisation_id> "Digital ID Integration"
   
   # Example (user_id=3, organisation_id=1):
   php scripts/create-api-key.php 3 1 "Digital ID Integration"
   ```

3. **Save the API Key**:
   - The script will output an API key (64-character hex string)
   - **IMPORTANT**: Save this key securely - it won't be shown again!

### Step 2: Configure Digital ID Settings

**Where to paste the API key:** Copy the API key from Staff Service and configure it in Digital ID's web interface (no need to access `.env` files).

#### Method A: Web Interface (Recommended - No File Access Required)

1. **Log in to Digital ID** as an organisation administrator
2. **Navigate to Admin** → **Organisation** → **Staff Service** (in the dropdown menu)
3. **Enable Staff Service Integration**:
   - Check the "Enable Staff Service Integration" checkbox
   - Enter the **Staff Service URL** (e.g., `http://localhost:8000` or `https://staff.yourdomain.com`)
   - Paste the **API Key** you copied from Staff Service in Step 1
   - Set the **Sync Interval** (default: 3600 seconds = 1 hour)
4. **Test the Connection** (optional):
   - Click "Test Connection" to verify the URL and API key are correct
5. **Save Settings**:
   - Click "Save Settings"
   - Settings are stored in the database and take effect immediately

**That's it!** No need to edit `.env` files or restart the server. The settings are stored in the database and work immediately.

#### Method B: .env File (Alternative - For Server Administrators)

If you prefer to configure via `.env` file (for server-level configuration):

1. **Locate or Create `.env` File**:
   - Navigate to your **Digital ID** project root directory
   - The `.env` file should be in the same directory as `config.php`
   - If `.env` doesn't exist, create it: `touch .env`

2. **Add Staff Service Configuration**:
   ```env
   USE_STAFF_SERVICE=true
   STAFF_SERVICE_URL=http://localhost:8000
   STAFF_SERVICE_API_KEY=your-api-key-from-staff-service-here
   STAFF_SYNC_INTERVAL=3600
   ```

3. **Important Notes**:
   - Replace `your-api-key-from-staff-service-here` with the actual API key
   - Replace `http://localhost:8000` with your actual Staff Service URL
   - No spaces around the `=` sign
   - Don't use quotes around values
   - Restart web server/PHP-FPM after editing

**Note:** Settings configured via the web interface take precedence over `.env` file settings.

### Step 3: Verify Configuration

1. **Check if Configuration is Loaded**:
   - Log in to Digital ID as an admin
   - Go to **Admin** → **Manage Employees**
   - You should see a "Sync from Staff Service" button if configuration is correct

2. **Test Connection**:
   - Click "Sync from Staff Service" button
   - If you see "Staff Service is not available", check:
     - Is `USE_STAFF_SERVICE=true` in `.env`?
     - Is `STAFF_SERVICE_URL` correct?
     - Is `STAFF_SERVICE_API_KEY` correct?
     - Can Digital ID server reach Staff Service URL? (test with `curl` or browser)

### Configuration in config.php

The configuration is automatically loaded from environment variables:

- `USE_STAFF_SERVICE` - Enable/disable integration (default: false)
- `STAFF_SERVICE_URL` - Base URL of Staff Service
- `STAFF_SERVICE_API_KEY` - API key for authentication
- `STAFF_SYNC_INTERVAL` - Periodic sync interval in seconds (default: 3600)

## Setup Steps

### 1. Run Database Migration

Run the migration to add Staff Service integration fields to the `employees` table:

```bash
mysql -u your_user -p your_database < sql/migrations/add_staff_service_integration.sql
```

Or via PHP:

```bash
php -r "require 'config/config.php'; \$db = getDbConnection(); \$sql = file_get_contents('sql/migrations/add_staff_service_integration.sql'); \$db->exec(\$sql);"
```

### 2. Link Existing Employees

Run the migration script to link existing employees to Staff Service:

```bash
php sql/migrations/migrate_to_staff_service.php
```

This script will:
- Match employees by `user_id` or `employee_reference`
- Link matching employees to Staff Service people records
- Perform initial sync of data

### 3. Configure Webhooks (Optional)

To receive real-time updates from Staff Service, configure webhooks in Staff Service to point to:

```
https://your-digital-id-domain.com/api/staff-service-webhook.php
```

Set the webhook secret in your `.env` file as `STAFF_SERVICE_WEBHOOK_SECRET`.

## Data Flow

### Sync Methods

1. **On-Demand Sync**: When an employee record is accessed, if it's linked to Staff Service and data is stale, it syncs automatically
2. **Periodic Sync**: Run the sync script via cron to sync all staff periodically
3. **Webhook Sync**: Real-time sync when Staff Service sends webhook events

### Sync Script

Run the periodic sync script manually or via cron:

```bash
# Sync all organisations
php scripts/sync-staff-service.php

# Sync specific organisation
php scripts/sync-staff-service.php 1
```

Add to crontab for automatic syncing:

```cron
# Sync every hour
0 * * * * cd /path/to/digital-id && php scripts/sync-staff-service.php >> /var/log/staff-sync.log 2>&1
```

## Admin Interface

### Employee Management

In the employee management pages (`/admin/employees.php` and `/admin/employees-edit.php`):

- **Sync Status**: Shows if employee is linked to Staff Service
- **Last Sync Time**: Displays when data was last synced
- **Sync Button**: Manual sync button to refresh data from Staff Service
- **Bulk Sync**: "Sync from Staff Service" button to sync all employees

### Linking Employees

Employees can be linked to Staff Service in two ways:

1. **Automatic**: When creating an employee, if a matching Staff Service person is found, it can be linked automatically
2. **Manual**: Through the admin interface, link existing employees to Staff Service person records

## Webhook Events

The webhook handler receives the following events from Staff Service:

- `person.created` - New staff member created
- `person.updated` - Staff member updated
- `person.deactivated` - Staff member deactivated
- `signature.uploaded` - Signature uploaded/updated
- `photo.updated` - Photo updated

## Signature Display

When an employee is linked to Staff Service and has a signature:

- Signature is automatically fetched from Staff Service API
- Signature URL is cached in `employees.signature_url`
- Signature is displayed on the digital ID card below the photo
- Signature updates automatically when synced

## Standalone Mode

If Staff Service integration is disabled or unavailable:

- Digital ID operates independently
- Employees are managed locally
- No external API calls are made
- All functionality works as before

## Getting Digital ID Cards for New Staff Members

When a new staff member is created in Staff Service, follow these steps to get them a digital ID card:

### Quick Steps

1. **Ensure User Account Exists**: Staff member needs a user account in Digital ID (or shared-auth if using shared authentication)

2. **Sync from Staff Service** (if integration enabled):
   - Go to **Admin** → **Manage Employees**
   - Click **"Sync from Staff Service"** button
   - This will automatically create employee records for staff members from Staff Service

3. **Or Create Manually**:
   - Go to **Admin** → **Manage Employees**
   - Click **"Create New Employee"**
   - Select user from dropdown
   - Enter employee number (should match Staff Service employee reference)
   - System will automatically link to Staff Service if integration enabled

4. **Upload Photo** (optional):
   - Staff member can upload through their profile, OR
   - Admin can upload directly in employee edit page

5. **View ID Card**:
   - Staff member logs in and navigates to "Digital ID Card" page
   - Card is automatically generated

For detailed instructions, see the [Digital ID Workflow Guide](../../people-management-service/docs/DIGITAL_ID_WORKFLOW.md) in Staff Service documentation.

## Troubleshooting

### Staff Service Not Available

If `StaffServiceClient::isAvailable()` returns false:

1. Check `STAFF_SERVICE_URL` is correct
2. Verify `STAFF_SERVICE_API_KEY` is valid
3. Ensure Staff Service is accessible from Digital ID server
4. Check firewall/network settings

### Sync Failures

If syncing fails:

1. Check error logs: `error_log()` messages
2. Verify API key has correct permissions
3. Ensure employee reference matches between systems
4. Check database connection and table structure

### Webhook Not Working

If webhooks aren't being received:

1. Verify webhook URL is accessible from Staff Service
2. Check webhook secret matches in both systems
3. Review webhook logs in Staff Service
4. Check firewall allows incoming webhook requests

## API Reference

### StaffServiceClient

- `getStaffMember($personId)` - Get staff member by person ID
- `getStaffByUserId($userId)` - Get staff by user ID
- `searchStaff($query)` - Search staff by name/email/reference
- `getStaffSignature($personId)` - Get signature URL
- `isAvailable()` - Check if Staff Service is reachable

### StaffSyncService

- `syncAllStaff($organisationId)` - Sync all staff for organisation
- `syncStaffMember($personId)` - Sync single staff member
- `syncStaffByUserId($userId)` - Sync by user ID
- `updateLocalEmployee($employeeId, $staffData)` - Update local employee record

### Employee Model

- `syncFromStaffService($personId, $employeeId)` - Sync employee from Staff Service
- `linkToStaffService($employeeId, $personId)` - Link employee to Staff Service person
- `createWithStaffService(...)` - Create employee with Staff Service link

## Data Mapping

| Staff Service | Digital ID |
|--------------|------------|
| `people.id` | `employees.staff_service_person_id` |
| `people.first_name` | `users.first_name` (via user_id) |
| `people.last_name` | `users.last_name` (via user_id) |
| `people.employee_reference` | `employees.employee_reference` |
| `people.photo_path` | `employees.photo_path` |
| `staff_profiles.signature_path` | `employees.signature_url` |
| `people.is_active` | `employees.is_active` |

