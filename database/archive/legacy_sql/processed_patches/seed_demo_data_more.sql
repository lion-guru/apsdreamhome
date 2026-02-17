-- More demo data for additional tables

-- Feedback
INSERT INTO feedback (customer_id, property_id, feedback_text, rating, created_at) VALUES
(1, 1, 'Excellent property and smooth process.', 5, NOW()),
(2, 2, 'Agent was very helpful!', 4, NOW());

-- Gallery
INSERT INTO gallery (property_id, image_url, caption, uploaded_at) VALUES
(1, 'images/villa1.jpg', 'Front view', NOW()),
(2, 'images/apartment1.jpg', 'Living room', NOW());

-- Testimonials
INSERT INTO testimonials (customer_id, testimonial_text, created_at) VALUES
(1, 'I found my dream home through APS Dream Home!', NOW()),
(2, 'Great experience and support from the team.', NOW());

-- Analytics (example, if analytics table exists)
INSERT INTO analytics (metric, value, recorded_at) VALUES
('total_users', 3, NOW()),
('total_properties', 2, NOW()),
('total_bookings', 2, NOW());
