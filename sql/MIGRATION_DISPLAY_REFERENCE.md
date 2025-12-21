# Migration Guide: Display Reference System

## Overview

This migration separates internal employee numbers (from HR/payroll systems) from display references (shown on digital ID cards).

## Changes

1. **New Fields:**
   - `employee_number` - Internal employee number from HR/payroll (required, non-editable)
   - `display_reference` - Reference shown on ID cards (auto-generated or manual)

2. **Organisation Configuration:**
   - `reference_prefix` - Prefix for display references (e.g., "SAMH")
   - `reference_pattern` - Pattern type: incremental, random_alphanumeric, or custom
   - `reference_start_number` - Starting number for incremental references
   - `reference_digits` - Number of digits for incremental references

## Migration Steps

1. **Run the migration SQL:**
   ```bash
   mysql -u your_user -p your_database < sql/add_display_reference.sql
   ```

2. **Or run manually:**
   - The SQL file will:
     - Add `display_reference` and `employee_number` columns
     - Copy existing `employee_reference` to both fields (preserving data)
     - Add unique constraint for display references
     - Add reference format configuration to organisations table

3. **Configure Reference Settings:**
   - Admins should visit: `admin/reference-settings.php`
   - Set prefix, pattern, and number format for auto-generation
   - Or choose "custom" to require manual entry

## Important Notes

- **Employee numbers cannot be edited** - They come from HR/payroll systems
- **Display references are shown on ID cards** - Employee numbers are not
- **Backwards compatibility** - Existing code using `employee_reference` will continue to work during transition
- **Auto-generation** - Display references can be auto-generated based on organisation settings

