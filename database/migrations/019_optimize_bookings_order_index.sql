-- Optimize bookings listing (status filter + booking_date DESC, created_at DESC)
-- Adds composite index to align with WHERE + ORDER BY to avoid filesort

ALTER TABLE `bookings` ADD INDEX `idx_bookings_status_booking_created`
  (`status`, `booking_date` DESC, `created_at` DESC);

