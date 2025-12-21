# Run Photo Approval Migration

The photo approval feature requires a database migration to add the necessary columns.

## Quick Fix

You need to run the SQL migration file on your production database.

### Option 1: Via Hostinger phpMyAdmin (Easiest)

1. Log into your Hostinger control panel
2. Go to **Databases** → **phpMyAdmin**
3. Select your database: `u248320297_digital_ids`
4. Click on the **SQL** tab
5. Copy and paste the contents of `sql/add_photo_approval.sql`
6. Click **Go** to execute

### Option 2: Via Hostinger File Manager + SQL Import

1. Upload `sql/add_photo_approval.sql` to your server
2. In Hostinger, go to **Databases** → **phpMyAdmin**
3. Select your database
4. Click **Import** tab
5. Choose the uploaded file and click **Go**

### Option 3: Via Command Line (if you have SSH access)

```bash
mysql -u u248320297_digitalids -p u248320297_digital_ids < sql/add_photo_approval.sql
```

## What This Migration Does

Adds the following columns to the `employees` table:
- `photo_approval_status` - Status of photo approval (pending, approved, rejected, none)
- `photo_pending_path` - Path to uploaded photo awaiting approval
- `photo_rejection_reason` - Reason if photo was rejected
- `photo_approved_at` - Timestamp when photo was approved
- `photo_approved_by` - User ID who approved the photo

## After Running the Migration

1. Refresh your browser
2. The Photos page should now work correctly
3. Users will be able to upload photos for approval

