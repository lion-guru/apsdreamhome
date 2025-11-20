-- Final comprehensive demo data for remaining modules

-- Property features and amenities
INSERT INTO property_features (property_id, feature_name, feature_value) VALUES
(1, 'Swimming Pool', 'Yes'),
(1, 'Garden', 'Yes'),
(1, 'Parking Spaces', '3'),
(1, 'Security System', 'Yes'),
(1, 'Furnished', 'Fully'),
(2, 'Balcony', 'Yes'),
(2, 'Parking Spaces', '1'),
(2, 'Security System', 'Yes'),
(2, 'Furnished', 'Semi');

-- Property transactions and payment history
INSERT INTO property_transactions (property_id, transaction_type, amount, payment_method, transaction_date, status, reference_number) VALUES
(1, 'booking', 1500000, 'bank_transfer', '2025-05-01', 'completed', 'TXN-20250501-001'),
(1, 'installment', 3500000, 'bank_transfer', '2025-05-10', 'completed', 'TXN-20250510-001'),
(2, 'booking', 700000, 'credit_card', '2025-05-02', 'completed', 'TXN-20250502-001'),
(2, 'installment', 1500000, 'bank_transfer', '2025-05-12', 'pending', 'TXN-20250512-001');

-- Communication history
INSERT INTO communication_logs (user_id, customer_id, communication_type, subject, message, sent_at, status) VALUES
(2, 1, 'email', 'Property Visit Confirmation', 'Your visit to Luxury Villa has been confirmed for May 20, 2025 at 14:00.', '2025-05-15 10:30:00', 'sent'),
(2, 2, 'email', 'Property Visit Confirmation', 'Your visit to City Apartment has been confirmed for May 21, 2025 at 11:00.', '2025-05-15 11:15:00', 'sent'),
(2, 1, 'sms', 'Visit Reminder', 'Reminder: Your property visit is scheduled for tomorrow at 14:00.', '2025-05-19 14:00:00', 'sent'),
(2, 1, 'email', 'Thank You for Your Visit', 'Thank you for visiting Luxury Villa. We hope you had a great experience.', '2025-05-15 16:30:00', 'sent');

-- Search logs for analytics
INSERT INTO search_logs (user_id, search_query, filters, results_count, session_id, search_date) VALUES
(3, 'luxury villa', 'price:10000000-20000000,bedrooms:4-5', 3, 'SESSION-001', NOW()),
(NULL, 'apartment city center', 'price:5000000-10000000,bedrooms:2-3', 5, 'SESSION-002', NOW()),
(3, 'house with garden', 'price:8000000-15000000,features:garden', 4, 'SESSION-003', NOW()),
(NULL, 'property near metro', 'location:city center', 8, 'SESSION-004', NOW());

-- User preferences
INSERT INTO user_preferences (user_id, preference_key, preference_value) VALUES
(1, 'dashboard_layout', 'compact'),
(1, 'notification_email', 'true'),
(1, 'notification_sms', 'false'),
(2, 'dashboard_layout', 'detailed'),
(2, 'notification_email', 'true'),
(2, 'notification_sms', 'true');

-- Property ratings and reviews
INSERT INTO property_ratings (property_id, customer_id, rating, review, review_date, status) VALUES
(1, 1, 5, 'Excellent property with amazing amenities. The villa exceeded our expectations.', '2025-05-16', 'approved'),
(2, 2, 4, 'Great apartment in a convenient location. Modern amenities and good value.', '2025-05-16', 'approved');

-- Agent commissions
INSERT INTO agent_commissions (agent_id, property_id, commission_amount, commission_percentage, transaction_id, payment_status, payment_date) VALUES
(2, 1, 150000, 1.0, 1, 'paid', '2025-05-10'),
(2, 2, 70000, 1.0, 3, 'pending', NULL);
