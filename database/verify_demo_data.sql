-- Verification script to check all tables and their data counts
-- This helps ensure all dashboard widgets have data to display

-- Get counts from all core tables
SELECT 'users' AS table_name, COUNT(*) AS record_count FROM users
UNION
SELECT 'properties' AS table_name, COUNT(*) AS record_count FROM properties
UNION
SELECT 'customers' AS table_name, COUNT(*) AS record_count FROM customers
UNION
SELECT 'leads' AS table_name, COUNT(*) AS record_count FROM leads
UNION
SELECT 'bookings' AS table_name, COUNT(*) AS record_count FROM bookings
UNION
SELECT 'property_visits' AS table_name, COUNT(*) AS record_count FROM property_visits
UNION
SELECT 'visit_reminders' AS table_name, COUNT(*) AS record_count FROM visit_reminders
UNION
SELECT 'visit_availability' AS table_name, COUNT(*) AS record_count FROM visit_availability
UNION
SELECT 'notifications' AS table_name, COUNT(*) AS record_count FROM notifications
UNION
SELECT 'feedback' AS table_name, COUNT(*) AS record_count FROM feedback
UNION
SELECT 'gallery' AS table_name, COUNT(*) AS record_count FROM gallery
UNION
SELECT 'testimonials' AS table_name, COUNT(*) AS record_count FROM testimonials
ORDER BY table_name;
