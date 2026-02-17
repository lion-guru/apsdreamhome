-- Demo data for Notification System

-- Notification Templates (if table exists)
INSERT IGNORE INTO notification_templates (type, title_template, message_template) VALUES
('new_lead', 'New Lead: {property_title}', 'You have received a new lead for {property_title} from {customer_name}.'),
('visit_scheduled', 'Visit Scheduled: {property_title}', '{customer_name} has scheduled a visit for {property_title} on {visit_date} at {visit_time}.'),
('visit_reminder', 'Visit Reminder: {property_title}', 'Reminder: You have a property visit for {property_title} on {visit_date} at {visit_time}.'),
('lead_status_change', 'Lead Status Updated: {property_title}', 'The status of your lead for {property_title} has been updated to {lead_status}.'),
('property_status_change', 'Property Status Change: {property_title}', 'The status of {property_title} has been changed to {property_status}.');

-- Notifications for various users and types
INSERT INTO notifications (user_id, type, title, message, status, link, created_at) VALUES
(1, 'info', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', 'dashboard.php', NOW()),
(2, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Rahul Sharma.', 'unread', 'leads.php?id=1', NOW()),
(2, 'visit', 'Visit Scheduled: City Apartment', 'Priya Singh has scheduled a visit for City Apartment on 2025-05-21 at 11:00.', 'unread', 'visits.php?id=2', NOW()),
(1, 'lead', 'Lead Status Updated: City Apartment', 'The lead for City Apartment has been updated to contacted.', 'read', 'leads.php?id=2', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'property', 'Property Status Change: Luxury Villa', 'The status of Luxury Villa has been changed to available.', 'read', 'properties.php?id=1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'visit', 'Visit Cancelled: Luxury Villa', 'The visit for Luxury Villa on 2025-05-16 has been cancelled.', 'read', 'visits.php?id=4', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'reminder', 'Visit Reminder: Luxury Villa', 'Reminder: You have a property visit for Luxury Villa tomorrow at 14:00.', 'unread', 'visits.php?id=1', NOW()),
(1, 'system', 'Database Backup Complete', 'The weekly database backup has been completed successfully.', 'read', '', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'feedback', 'New Feedback Received', 'You have received a 5-star feedback for Luxury Villa.', 'unread', 'feedback.php', NOW());
