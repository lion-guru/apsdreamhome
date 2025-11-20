-- Auto-generated unique constraints migration
-- Adds UNIQUE constraints for common identifiers when no duplicates exist.

-- SKIP: UNIQUE on `users`(email) already exists
-- SKIP: `users`.`username` missing
-- SKIP: `customers`.`email` missing
-- SKIP: `associates`.`referral_code` missing
ALTER TABLE `api_keys` ADD UNIQUE `uq_api_keys_api_key` (`api_key`);

