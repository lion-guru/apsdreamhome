-- Final enhancements and edge case data for complete dashboard visualization

-- Mobile app usage analytics
INSERT INTO app_usage_analytics (user_id, platform, session_duration, screens_viewed, actions_performed, app_version, session_date) VALUES
(1, 'android', 1250, 8, 12, '2.3.1', NOW()),
(2, 'ios', 950, 6, 8, '2.3.1', NOW()),
(3, 'android', 720, 5, 6, '2.3.1', NOW());

-- Customer journey tracking
INSERT INTO customer_journey (customer_id, stage, entry_date, exit_date, conversion_source, notes) VALUES
(1, 'awareness', '2025-04-01', '2025-04-10', 'social_media', 'Found via Facebook ad'),
(1, 'consideration', '2025-04-10', '2025-04-25', 'property_listing', 'Viewed Luxury Villa listing 5 times'),
(1, 'decision', '2025-04-25', '2025-05-01', 'agent_contact', 'Contacted agent for viewing'),
(1, 'action', '2025-05-01', NULL, 'site_visit', 'Scheduled multiple visits'),
(2, 'awareness', '2025-04-05', '2025-04-15', 'search_engine', 'Found via Google search'),
(2, 'consideration', '2025-04-15', '2025-04-28', 'property_comparison', 'Compared 3 apartments'),
(2, 'decision', '2025-04-28', '2025-05-02', 'virtual_tour', 'Engaged with virtual tour'),
(2, 'action', '2025-05-02', NULL, 'booking', 'Made initial booking payment');

-- Seasonal promotions
INSERT INTO seasonal_promotions (name, description, discount_percentage, start_date, end_date, applicable_properties, promotion_code, status) VALUES
('Summer Special', 'Special summer discounts on premium properties', 5.0, '2025-05-01', '2025-06-30', 'premium', 'SUMMER2025', 'active'),
('Festive Offer', 'Festive season special offers', 7.5, '2025-10-01', '2025-11-15', 'all', 'FESTIVE2025', 'scheduled'),
('New Year Deal', 'New year special pricing', 10.0, '2025-12-15', '2026-01-15', 'luxury', 'NEWYEAR2026', 'scheduled');

-- Email campaign performance
INSERT INTO email_campaigns (campaign_name, subject_line, sent_date, total_recipients, open_rate, click_rate, conversion_rate, status) VALUES
('May Newsletter', 'Discover Your Dream Home This Summer', '2025-05-01', 1250, 28.5, 12.3, 3.5, 'completed'),
('New Property Alert', 'Exclusive New Properties Just Listed', '2025-05-10', 850, 32.7, 18.5, 5.2, 'completed'),
('Visit Reminder', 'Your Upcoming Property Visits This Week', '2025-05-15', 120, 85.3, 45.8, 12.5, 'completed');

-- Agent territory mapping
INSERT INTO agent_territories (agent_id, location_name, property_types, assignment_date, performance_score) VALUES
(2, 'Delhi North', 'villa,apartment', '2025-01-01', 8.5),
(2, 'Delhi Central', 'apartment,commercial', '2025-01-01', 7.8),
(3, 'Mumbai South', 'villa,apartment,penthouse', '2025-01-01', 8.2);

-- Feedback response tracking
INSERT INTO feedback_responses (feedback_id, responder_id, response_text, response_date, customer_satisfaction) VALUES
(1, 2, 'Thank you for your positive feedback! We\'re delighted you enjoyed the property.', '2025-05-17', NULL),
(2, 2, 'We appreciate your feedback and are glad you had a good experience.', '2025-05-17', NULL);

-- System health monitoring
INSERT INTO system_health (component, status, uptime_percentage, last_issue_date, last_maintenance_date, notes) VALUES
('Database', 'healthy', 99.98, '2025-04-10', '2025-05-01', 'Regular maintenance completed'),
('Web Server', 'healthy', 99.95, '2025-04-15', '2025-05-01', 'Performance optimization done'),
('Email Service', 'healthy', 99.90, '2025-04-20', '2025-05-01', 'SMTP configuration updated'),
('Notification System', 'healthy', 99.92, '2025-04-18', '2025-05-01', 'Push notification service upgraded');
