-- Rollback for Migration: Add User Preferences Table
-- Version: 1.0.1
-- Created: 2025-05-18 00:34:08

-- Write your rollback SQL statements below this line
-- Each statement must end with a semicolon

-- Drop the user_preferences table
DROP TABLE IF EXISTS user_preferences;

-- Rollback verification
-- Add SELECT queries to verify the rollback was successful
SHOW TABLES LIKE 'user_preferences';
