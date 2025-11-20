-- Rollback for Migration: Add API Authentication Tables
-- Version: 1.0.2
-- Created: 2025-05-18 00:44:03

-- Drop foreign key constraints first
ALTER TABLE api_request_logs
DROP FOREIGN KEY api_request_logs_ibfk_1;

-- Drop API request logs table
DROP TABLE IF EXISTS api_request_logs;

-- Drop API keys table
DROP TABLE IF EXISTS api_keys;

-- Remove API permissions from users table
ALTER TABLE users 
DROP COLUMN IF EXISTS api_access,
DROP COLUMN IF EXISTS api_rate_limit;

-- Rollback verification
SHOW TABLES LIKE 'api_keys';
SHOW TABLES LIKE 'api_request_logs';
