-- Demo data for Analytics & Reporting

-- Lead source analytics
INSERT INTO lead_analytics (source, count, conversion_rate, month, year) VALUES
('website', 45, 22.5, 5, 2025),
('visit_schedule', 30, 33.3, 5, 2025),
('referral', 15, 40.0, 5, 2025),
('direct', 25, 28.0, 5, 2025),
('other', 10, 20.0, 5, 2025);

-- Property performance metrics
INSERT INTO property_analytics (property_type, views, inquiries, visits, conversion_rate, month, year) VALUES
('villa', 250, 35, 12, 8.5, 5, 2025),
('apartment', 320, 48, 18, 10.2, 5, 2025),
('house', 180, 25, 8, 7.8, 5, 2025),
('land', 90, 10, 3, 5.5, 5, 2025);

-- Agent performance tracking
INSERT INTO agent_analytics (agent_id, properties_managed, leads_assigned, visits_conducted, deals_closed, month, year) VALUES
(2, 12, 28, 15, 3, 5, 2025),
(3, 8, 18, 10, 2, 5, 2025);

-- Revenue analytics
INSERT INTO revenue_analytics (month, year, total_revenue, booking_revenue, commission_revenue) VALUES
(1, 2025, 2500000, 2000000, 500000),
(2, 2025, 3200000, 2700000, 500000),
(3, 2025, 2800000, 2300000, 500000),
(4, 2025, 3500000, 3000000, 500000),
(5, 2025, 4200000, 3500000, 700000);
