-- Final schema-accurate demo data for feedback, gallery, and testimonials

-- Feedback (if only feedback_text, rating, created_at)
INSERT INTO feedback (feedback_text, rating, created_at) VALUES
('Excellent property and smooth process.', 5, NOW()),
('Agent was very helpful!', 4, NOW());

-- Gallery (if only image_url, caption)
INSERT INTO gallery (image_url, caption) VALUES
('images/villa1.jpg', 'Front view'),
('images/apartment1.jpg', 'Living room');

-- Testimonials (if only testimonial_text, created_at)
INSERT INTO testimonials (testimonial_text, created_at) VALUES
('I found my dream home through APS Dream Home!', NOW()),
('Great experience and support from the team.', NOW());
