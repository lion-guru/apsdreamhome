-- Demo data for Visit Management System

-- Visit Availability (property viewing time slots)
INSERT INTO visit_availability (property_id, day_of_week, start_time, end_time, max_visits_per_slot) VALUES
(1, 'Monday', '10:00:00', '18:00:00', 3),
(1, 'Wednesday', '10:00:00', '18:00:00', 3),
(1, 'Friday', '10:00:00', '18:00:00', 3),
(2, 'Tuesday', '09:00:00', '17:00:00', 2),
(2, 'Thursday', '09:00:00', '17:00:00', 2),
(2, 'Saturday', '11:00:00', '15:00:00', 4);

-- Property Visits (if not already populated)
INSERT INTO property_visits (customer_id, property_id, lead_id, visit_date, visit_time, status, feedback, rating)
VALUES
(1, 1, 1, '2025-05-20', '14:00:00', 'scheduled', NULL, NULL),
(2, 2, 2, '2025-05-21', '11:00:00', 'scheduled', NULL, NULL),
(1, 2, 1, '2025-05-15', '10:00:00', 'completed', 'Property was exactly as described. Very satisfied.', 5),
(2, 1, 2, '2025-05-16', '15:00:00', 'cancelled', 'Had a scheduling conflict', NULL);

-- Visit Reminders
INSERT INTO visit_reminders (visit_id, reminder_type, status, scheduled_at, sent_at)
VALUES
(1, '24h', 'pending', DATE_SUB(CONCAT('2025-05-20', ' ', '14:00:00'), INTERVAL 24 HOUR), NULL),
(1, '1h', 'pending', DATE_SUB(CONCAT('2025-05-20', ' ', '14:00:00'), INTERVAL 1 HOUR), NULL),
(2, '24h', 'pending', DATE_SUB(CONCAT('2025-05-21', ' ', '11:00:00'), INTERVAL 24 HOUR), NULL),
(2, '1h', 'pending', DATE_SUB(CONCAT('2025-05-21', ' ', '11:00:00'), INTERVAL 1 HOUR), NULL),
(3, '24h', 'sent', DATE_SUB(CONCAT('2025-05-15', ' ', '10:00:00'), INTERVAL 24 HOUR), DATE_SUB(CONCAT('2025-05-15', ' ', '10:00:00'), INTERVAL 24 HOUR)),
(3, '1h', 'sent', DATE_SUB(CONCAT('2025-05-15', ' ', '10:00:00'), INTERVAL 1 HOUR), DATE_SUB(CONCAT('2025-05-15', ' ', '10:00:00'), INTERVAL 1 HOUR)),
(4, '24h', 'sent', DATE_SUB(CONCAT('2025-05-16', ' ', '15:00:00'), INTERVAL 24 HOUR), DATE_SUB(CONCAT('2025-05-16', ' ', '15:00:00'), INTERVAL 24 HOUR)),
(4, '1h', 'cancelled', DATE_SUB(CONCAT('2025-05-16', ' ', '15:00:00'), INTERVAL 1 HOUR), NULL);
