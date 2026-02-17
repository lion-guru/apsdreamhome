-- Final seed data for any remaining tables that might need population
-- This ensures ALL dashboard widgets have data to display

-- In case any of these tables exist but weren't populated yet

-- Property types reference table
INSERT IGNORE INTO property_types (type_name, description, display_order) VALUES
('villa', 'Luxury standalone houses with premium amenities', 1),
('apartment', 'Multi-unit residential buildings with shared facilities', 2),
('penthouse', 'Luxury apartments on the top floor of high-rise buildings', 3),
('house', 'Standard residential houses', 4),
('land', 'Vacant land plots for development', 5),
('commercial', 'Properties for business and commercial use', 6);

-- Cities and locations
INSERT IGNORE INTO locations (name, state, is_active, popularity_score) VALUES
('Delhi North', 'Delhi', 1, 85),
('Delhi Central', 'Delhi', 1, 90),
('Delhi South', 'Delhi', 1, 88),
('Mumbai South', 'Maharashtra', 1, 95),
('Mumbai Suburban', 'Maharashtra', 1, 92),
('Bangalore Central', 'Karnataka', 1, 88),
('Bangalore East', 'Karnataka', 1, 85);

-- User activity logs (for dashboard activity widgets)
INSERT IGNORE INTO user_activity_logs (user_id, activity_type, activity_description, ip_address, created_at) VALUES
(1, 'login', 'User logged in', '192.168.1.100', NOW()),
(1, 'view', 'Viewed dashboard', '192.168.1.100', NOW()),
(1, 'update', 'Updated property #1', '192.168.1.100', NOW()),
(2, 'login', 'User logged in', '192.168.1.101', NOW()),
(2, 'create', 'Created new property listing', '192.168.1.101', NOW()),
(2, 'update', 'Updated lead #2', '192.168.1.101', NOW());

-- Dashboard settings (for user customization)
INSERT IGNORE INTO dashboard_settings (user_id, widget_id, is_visible, position, size) VALUES
(1, 'recent_properties', 1, 1, 'large'),
(1, 'recent_leads', 1, 2, 'medium'),
(1, 'revenue_chart', 1, 3, 'large'),
(1, 'activity_feed', 1, 4, 'medium'),
(1, 'visit_calendar', 1, 5, 'large'),
(2, 'recent_properties', 1, 1, 'large'),
(2, 'recent_leads', 1, 2, 'large'),
(2, 'visit_calendar', 1, 3, 'large'),
(2, 'activity_feed', 1, 4, 'medium');

-- System configuration
INSERT IGNORE INTO system_config (config_key, config_value, description, is_editable) VALUES
('site_name', 'APS Dream Home', 'Website name', 1),
('admin_email', 'admin@apsdreamhome.com', 'Admin contact email', 1),
('items_per_page', '10', 'Number of items to display per page', 1),
('enable_notifications', 'true', 'Enable system notifications', 1),
('maintenance_mode', 'false', 'Put system in maintenance mode', 1);
