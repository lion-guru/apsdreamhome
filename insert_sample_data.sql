-- Sample data insertion script for APS Dream Home
USE apsdreamhome;

-- Insert sample users (using the existing user table structure)
INSERT INTO user (name, email, mobile, password, role_id, status) VALUES
('Admin User', 'admin@apsdreamhome.com', '9876543210', MD5('admin123'), 1, 1),
('Agent One', 'agent1@apsdreamhome.com', '9876543211', MD5('agent123'), 2, 1),
('Customer One', 'customer1@apsdreamhome.com', '9876543212', MD5('customer123'), 3, 1),
('Manager One', 'manager1@apsdreamhome.com', '9876543213', MD5('manager123'), 4, 1);

-- Insert sample properties
INSERT INTO property (title, description, price, location, city, state, status, type, bedrooms, bathrooms, area) VALUES
('2BHK Luxury Apartment', 'Beautiful 2BHK apartment with modern amenities and great view', 2500000, 'Andheri West', 'Mumbai', 'Maharashtra', 'available', 'residential', 2, 2, 1200.00),
('Commercial Shop', 'Prime location commercial shop in business district with high footfall', 5000000, 'Connaught Place', 'Delhi', 'Delhi', 'available', 'commercial', 0, 2, 800.00),
('Agricultural Land', '10 acres fertile agricultural land suitable for farming', 1500000, 'Ludhiana District', 'Ludhiana', 'Punjab', 'available', 'land', 0, 0, 435600.00),
('Residential Plot', '500 sq yards plot in gated community with all amenities', 800000, 'Whitefield', 'Bangalore', 'Karnataka', 'available', 'land', 0, 0, 4500.00),
('3BHK Premium Villa', 'Luxurious 3BHK villa with private garden and parking', 8500000, 'Bandra', 'Mumbai', 'Maharashtra', 'available', 'residential', 3, 3, 2500.00);

-- Insert sample pages
INSERT INTO pages (title, content, slug, status) VALUES
('About Us', 'APS Dream Home is a leading real estate company providing premium property solutions across India. We specialize in residential, commercial, and agricultural properties with a commitment to excellence and customer satisfaction.', 'about-us', 1),
('Contact Us', 'Get in touch with us for all your real estate needs. Our expert team is ready to assist you with property buying, selling, and investment opportunities.', 'contact-us', 1),
('Our Services', 'We offer comprehensive real estate services including property buying, selling, renting, property management, and investment consulting. Our services cover residential, commercial, and agricultural properties.', 'services', 1),
('Privacy Policy', 'Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information when you use our services.', 'privacy-policy', 1);

-- Insert sample FAQs
INSERT INTO faqs (question, answer, status) VALUES
('How do I buy a property?', 'To buy a property, browse our listings, contact our agents, schedule viewings, and complete the purchase process with our assistance.', 1),
('What documents are needed for property purchase?', 'You will need ID proof, address proof, income proof, and property-specific documents. Our team will guide you through the complete documentation process.', 1),
('Do you provide property management services?', 'Yes, we offer comprehensive property management services including maintenance, tenant management, and rental collection.', 1),
('Can I sell my property through APS Dream Home?', 'Absolutely! We provide complete property selling services including valuation, marketing, and closing the deal with potential buyers.', 1);

SELECT 'All sample data inserted successfully!' as status;