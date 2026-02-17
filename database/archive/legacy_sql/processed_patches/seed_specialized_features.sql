-- Specialized features and advanced modules demo data

-- Property investment analytics
INSERT INTO property_investment_analytics (property_id, purchase_price, current_value, annual_return, rental_yield, appreciation_rate, last_updated) VALUES
(1, 12000000, 15000000, 8.5, 5.2, 7.8, NOW()),
(2, 6000000, 7000000, 7.2, 4.8, 6.5, NOW());

-- Market trends data
INSERT INTO market_trends (location, property_type, avg_price, price_change_percentage, demand_index, supply_index, month, year) VALUES
('Delhi', 'villa', 14500000, 3.5, 8.2, 6.5, 5, 2025),
('Delhi', 'apartment', 6800000, 2.8, 7.5, 7.2, 5, 2025),
('Mumbai', 'villa', 18000000, 4.2, 8.5, 5.8, 5, 2025),
('Mumbai', 'apartment', 8500000, 3.7, 8.0, 6.2, 5, 2025);

-- Agent training and certification
INSERT INTO agent_certifications (agent_id, certification_name, issuing_authority, issue_date, expiry_date, status) VALUES
(2, 'Certified Real Estate Professional', 'Indian Real Estate Association', '2024-05-15', '2026-05-15', 'active'),
(2, 'Luxury Property Specialist', 'Premium Property Council', '2024-08-10', '2026-08-10', 'active');

-- Customer support tickets
INSERT INTO support_tickets (customer_id, subject, description, priority, status, created_at, assigned_to, resolved_at) VALUES
(1, 'Unable to schedule visit', 'I am trying to schedule a visit but getting an error.', 'medium', 'resolved', '2025-05-10 09:30:00', 1, '2025-05-10 11:45:00'),
(2, 'Payment confirmation not received', 'I made a payment but did not receive confirmation.', 'high', 'in_progress', '2025-05-15 14:20:00', 1, NULL);

-- Property maintenance requests
INSERT INTO maintenance_requests (property_id, requester_id, request_type, description, priority, status, created_at, scheduled_date) VALUES
(1, 2, 'repair', 'Swimming pool pump needs repair', 'medium', 'scheduled', '2025-05-12 10:15:00', '2025-05-18 09:00:00'),
(2, 2, 'inspection', 'Annual property inspection', 'low', 'completed', '2025-05-05 11:30:00', '2025-05-10 10:00:00');

-- Legal documents and compliance
INSERT INTO legal_documents (property_id, document_type, document_name, issuing_authority, issue_date, expiry_date, status) VALUES
(1, 'occupancy_certificate', 'OC-2024-VIL-001', 'Delhi Development Authority', '2024-01-15', NULL, 'valid'),
(1, 'property_tax', 'PT-2025-VIL-001', 'Municipal Corporation of Delhi', '2025-04-01', '2026-03-31', 'valid'),
(2, 'occupancy_certificate', 'OC-2023-APT-002', 'Mumbai Municipal Corporation', '2023-06-10', NULL, 'valid'),
(2, 'property_tax', 'PT-2025-APT-002', 'Mumbai Municipal Corporation', '2025-04-01', '2026-03-31', 'valid');

-- Virtual tour data
INSERT INTO virtual_tours (property_id, tour_url, created_by, created_at, view_count, average_duration) VALUES
(1, 'https://tours.apsdreamhome.com/luxury-villa-360', 2, '2025-04-20', 45, 320),
(2, 'https://tours.apsdreamhome.com/city-apartment-360', 2, '2025-04-22', 38, 280);
