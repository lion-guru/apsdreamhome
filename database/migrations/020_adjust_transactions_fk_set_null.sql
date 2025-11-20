-- Migration: 020_adjust_transactions_fk_set_null.sql
-- Purpose: Adjust transactions.customer_id FK to ON DELETE SET NULL
-- Notes:
-- - Ensures customer deletions do not cascade-remove historical transactions.
-- - Makes customer_id nullable to allow SET NULL behavior.

SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing FK (assumes standard name; adjust if different)
ALTER TABLE `transactions` DROP FOREIGN KEY `fk_transactions_customer_id`;

-- Ensure column is nullable to support SET NULL
ALTER TABLE `transactions` MODIFY COLUMN `customer_id` INT NULL;

-- Recreate FK with ON DELETE SET NULL, ON UPDATE CASCADE
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_customer_id`
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

