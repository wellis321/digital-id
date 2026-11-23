# Run Organisational Units Migration

The organisational structure page requires database tables that haven't been added to production yet.

## Quick Fix

Run the SQL migration file on your production database.

### Option 1: Via Hostinger phpMyAdmin (Easiest)

1. Log into your Hostinger control panel
2. Go to **Databases** → **phpMyAdmin**
3. Select your database: `u248320297_digital_ids`
4. Click on the **SQL** tab
5. Copy and paste the contents of `sql/add_organisational_units_safe.sql`
6. Click **Go** to execute

### Option 2: Via Command Line (if you have SSH access)

```bash
mysql -u u248320297_digitalids -p u248320297_digital_ids < sql/add_organisational_units_safe.sql
```

## What This Migration Does

Creates the following tables:
- `organisational_units` - Stores teams, departments, areas, regions, etc. with hierarchical structure
- `organisational_unit_members` - Links users to organisational units with roles

## After Running the Migration

1. Refresh your browser
2. The Organisational Structure page should now work correctly
3. You can create organisational units and assign users to them

## Note

This migration is safe to run multiple times - it checks for existing tables before creating them.

## Alternative: Simple Version (Recommended)

If the safe version doesn't work in your SQL client, use the simpler version:

**File: `sql/add_organisational_units_simple.sql`**

This uses `CREATE TABLE IF NOT EXISTS` which is simpler and works in most SQL clients. It's safe to run multiple times - if the tables already exist, it will just skip creating them.

