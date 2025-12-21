-- SQL Script to Fix User Verification
-- Run this to properly verify a user's email and activate their account

-- Replace 1 with the actual user ID you want to verify
UPDATE users 
SET email_verified = TRUE, 
    is_active = TRUE,
    verification_token = NULL,
    verification_token_expires_at = NULL
WHERE id = 1;

-- Or verify by email:
-- UPDATE users 
-- SET email_verified = TRUE, 
--     is_active = TRUE,
--     verification_token = NULL,
--     verification_token_expires_at = NULL
-- WHERE email = 'williamjamesellis@example.com';

-- Verify the update:
SELECT id, email, first_name, last_name, email_verified, is_active, verification_token
FROM users 
WHERE id = 1;

