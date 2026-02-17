-- Demo data for Advanced Features

-- Customer engagement metrics
INSERT INTO customer_engagement (customer_id, last_login, page_views, property_views, inquiries_sent, visits_scheduled, feedback_submitted, month, year) VALUES
(1, '2025-05-16 14:30:00', 45, 12, 3, 2, 1, 5, 2025),
(2, '2025-05-15 10:15:00', 32, 8, 2, 1, 1, 5, 2025);

-- Marketing campaign tracking
INSERT INTO marketing_campaigns (name, start_date, end_date, budget, leads_generated, conversion_rate, roi, status) VALUES
('Summer Property Showcase', '2025-05-01', '2025-05-31', 50000, 35, 8.5, 12.3, 'active'),
('Premium Villa Promotion', '2025-04-15', '2025-05-15', 30000, 22, 10.2, 15.7, 'completed'),
('First-Time Buyer Special', '2025-05-10', '2025-06-10', 40000, 18, 7.8, 9.5, 'active');

-- Property documents
INSERT INTO property_documents (property_id, document_type, file_name, uploaded_by, upload_date, status) VALUES
(1, 'floor_plan', 'luxury_villa_floor_plan.pdf', 2, '2025-05-10', 'active'),
(1, 'legal_document', 'luxury_villa_ownership.pdf', 2, '2025-05-10', 'active'),
(2, 'floor_plan', 'city_apartment_floor_plan.pdf', 2, '2025-05-12', 'active'),
(2, 'brochure', 'city_apartment_brochure.pdf', 2, '2025-05-12', 'active');

-- Agent schedule
INSERT INTO agent_schedule (agent_id, event_type, title, description, start_time, end_time, status) VALUES
(2, 'visit', 'Property Visit: Luxury Villa', 'Meeting with Rahul Sharma', '2025-05-20 14:00:00', '2025-05-20 15:00:00', 'confirmed'),
(2, 'visit', 'Property Visit: City Apartment', 'Meeting with Priya Singh', '2025-05-21 11:00:00', '2025-05-21 12:00:00', 'confirmed'),
(2, 'meeting', 'Team Meeting', 'Weekly sales team meeting', '2025-05-18 10:00:00', '2025-05-18 11:30:00', 'confirmed'),
(2, 'personal', 'Lunch Break', '', '2025-05-17 13:00:00', '2025-05-17 14:00:00', 'confirmed');

-- System activity logs
INSERT INTO system_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, created_at) VALUES
(1, 'login', 'user', 1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW()),
(1, 'view', 'property', 1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW()),
(2, 'login', 'user', 2, '192.168.1.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', NOW()),
(2, 'update', 'property', 2, '192.168.1.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', NOW()),
(1, 'create', 'lead', 1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW());
