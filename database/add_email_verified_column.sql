-- Add email_verified_at column to users table
ALTER TABLE users
ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL
AFTER status;
