-- Insert sample testimonials
INSERT INTO `testimonials` (`name`, `email`, `rating`, `testimonial`, `status`, `created_at`) VALUES
('Rahul Sharma', 'rahul@example.com', 5, 'Working with APS Dream Home was an amazing experience. They helped me find my dream home within my budget and made the entire process smooth and hassle-free.', 'approved', NOW() - INTERVAL 7 DAY),
('Priya Patel', 'priya@example.com', 4, 'The team at APS Dream Home provided exceptional service. They understood my requirements perfectly and showed me properties that matched exactly what I was looking for.', 'approved', NOW() - INTERVAL 5 DAY),
('Amit Singh', 'amit@example.com', 5, 'The rental management service from APS Dream Home has been exceptional. They handle everything professionally and I never have to worry about my property. Highly recommended!', 'approved', NOW() - INTERVAL 3 DAY),
('Neha Gupta', 'neha@example.com', 5, 'I was a first-time homebuyer and the team at APS Dream Home made the process so easy. They were patient, knowledgeable, and always available to answer my questions.', 'approved', NOW() - INTERVAL 2 DAY),
('Vikram Mehta', 'vikram@example.com', 4, 'Great experience working with APS Dream Home. They have a wide selection of properties and their agents are very professional and helpful.', 'approved', NOW() - INTERVAL 1 DAY);
