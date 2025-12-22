# Run Employee Columns Migration

The employees page requires database columns that haven't been added to production yet.

## Quick Fix

Run the SQL migration file on your production database.

### Option 1: Via Hostinger phpMyAdmin (Easiest)

1. Log into your Hostinger control panel
2. Go to **Databases** → **phpMyAdmin**
3. Select your database: `u248320297_digital_ids`
4. Click on the **SQL** tab
5. Copy and paste the contents of `sql/add_employee_columns_safe.sql`
6. Click **Go** to execute

### Option 2: Via Command Line (if you have SSH access)

```bash
mysql -u u248320297_digitalids -p u248320297_digital_ids < sql/add_employee_columns_safe.sql
```

## What This Migration Does

Adds the following columns to the `employees` table:
- `display_reference` - Reference shown on ID cards (auto-generated or manual)
- `employee_number` - Internal employee number from HR/payroll (required, non-editable)

Also:
- Copies existing `employee_reference` data to both new columns (preserves existing data)
- Adds unique constraint for `display_reference` within organisation
- Adds indexes for faster lookups

## After Running the Migration

1. Refresh your browser
2. The Employees page should now work correctly
3. You can create employee records with employee numbers and display references

## Note

This migration is safe to run multiple times - it checks for existing columns before adding them.

