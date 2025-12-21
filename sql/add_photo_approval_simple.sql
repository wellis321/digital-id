-- Migration: Add Photo Approval System (Simple Version)
-- Allows users to upload photos and requires admin approval before use
-- Use this version if the safe version doesn't work in your SQL client

-- Add photo approval status and pending photo path to employees table
-- Run each ALTER statement separately if you get errors

-- Step 1: Add photo_approval_status (skip if already exists)
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS photo_approval_status ENUM('pending', 'approved', 'rejected', 'none') DEFAULT 'none' AFTER photo_path;

-- Step 2: Add photo_pending_path (skip if already exists)
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS photo_pending_path VARCHAR(255) NULL COMMENT 'Path to uploaded photo awaiting approval' AFTER photo_approval_status;

-- Step 3: Add photo_rejection_reason (skip if already exists)
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS photo_rejection_reason TEXT NULL COMMENT 'Reason if photo was rejected' AFTER photo_pending_path;

-- Step 4: Add photo_approved_at (skip if already exists)
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS photo_approved_at TIMESTAMP NULL AFTER photo_rejection_reason;

-- Step 5: Add photo_approved_by (skip if already exists)
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS photo_approved_by INT NULL AFTER photo_approved_at;

-- Step 6: Add foreign key (skip if already exists)
-- Note: Some MySQL versions don't support IF NOT EXISTS for foreign keys
-- If this fails, the foreign key may already exist - that's okay
ALTER TABLE employees 
ADD CONSTRAINT fk_photo_approved_by FOREIGN KEY (photo_approved_by) REFERENCES users(id) ON DELETE SET NULL;

-- Step 7: Add index (skip if already exists)
ALTER TABLE employees 
ADD INDEX IF NOT EXISTS idx_photo_approval_status (photo_approval_status);

