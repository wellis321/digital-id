# Database Setup Instructions

## Quick Setup (Recommended)

Use the combined schema file that includes everything in the correct order:

```sql
source sql/complete_schema.sql
```

## Manual Setup (If you prefer step-by-step)

1. **First, create the core authentication tables:**
   ```sql
   source shared-auth/migrations/core_schema.sql
   ```

2. **Then, create the digital ID application tables:**
   ```sql
   source sql/schema.sql
   ```

3. **Finally, add Entra integration columns (optional):**
   ```sql
   source sql/migration_entra_columns.sql
   ```

## Important Notes

- The `schema.sql` file requires the core authentication tables to exist first
- If you get "Failed to open the referenced table 'users'" error, you need to run the shared-auth migrations first
- The `complete_schema.sql` file includes everything in the correct order and is the easiest way to set up the database

## Database Creation

Before running any migrations, create the database:

```sql
CREATE DATABASE digital_id CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE digital_id;
```

Then run one of the setup methods above.

