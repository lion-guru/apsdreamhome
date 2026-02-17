INSERT INTO bookings (user_id, property_id, booking_date, amount, status, customer_id, property_type, installment_plan, created_at, updated_at)
VALUES
(1, 201, '2025-04-15', 2500000, 'confirmed', 1, 1, 'Standard', '2025-04-15 10:30:00', '2025-04-15 10:30:00'),
(2, 202, '2025-04-16', 3200000, 'pending', 2, 2, 'Premium', '2025-04-16 12:10:00', '2025-04-16 12:10:00'),
(3, 203, '2025-04-17', 1800000, 'confirmed', 3, 1, 'Standard', '2025-04-17 09:45:00', '2025-04-17 09:45:00'),
(4, 204, '2025-04-17', 4000000, 'cancelled', 4, 2, 'Premium', '2025-04-17 14:05:00', '2025-04-17 14:05:00'),
(5, 205, '2025-04-18', 2700000, 'confirmed', 5, 1, 'Standard', '2025-04-18 16:20:00', '2025-04-18 16:20:00');

INSERT INTO transactions (user_id, amount, type, date, description, ref_id, created_at, updated_at)
VALUES
(1, 2500000, 'booking', '2025-04-15', 'Booking for Property 201', 'BK20250415A', '2025-04-15 10:31:00', '2025-04-15 10:31:00'),
(2, 3200000, 'booking', '2025-04-16', 'Booking for Property 202', 'BK20250416B', '2025-04-16 12:11:00', '2025-04-16 12:11:00'),
(3, 1800000, 'booking', '2025-04-17', 'Booking for Property 203', 'BK20250417C', '2025-04-17 09:46:00', '2025-04-17 09:46:00'),
(4, 4000000, 'booking', '2025-04-17', 'Booking for Property 204', 'BK20250417D', '2025-04-17 14:06:00', '2025-04-17 14:06:00'),
(5, 2700000, 'booking', '2025-04-18', 'Booking for Property 205', 'BK20250418E', '2025-04-18 16:21:00', '2025-04-18 16:21:00');
