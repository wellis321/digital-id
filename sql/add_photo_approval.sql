-- Migration: Add Photo Approval System
-- Allows users to upload photos and requires admin approval before use

-- Add photo approval status and pending photo path to employees table
ALTER TABLE employees 
ADD COLUMN photo_approval_status ENUM('pending', 'approved', 'rejected', 'none') DEFAULT 'none' AFTER photo_path,
ADD COLUMN photo_pending_path VARCHAR(255) NULL COMMENT 'Path to uploaded photo awaiting approval' AFTER photo_approval_status,
ADD COLUMN photo_rejection_reason TEXT NULL COMMENT 'Reason if photo was rejected' AFTER photo_pending_path,
ADD COLUMN photo_approved_at TIMESTAMP NULL AFTER photo_rejection_reason,
ADD COLUMN photo_approved_by INT NULL AFTER photo_approved_at,
ADD FOREIGN KEY (photo_approved_by) REFERENCES users(id) ON DELETE SET NULL,
ADD INDEX idx_photo_approval_status (photo_approval_status);

