-- Insert default footer settings
INSERT IGNORE INTO site_settings (setting_name, value) VALUES
('footer_links', '[
    {"text": "Home", "url": "/"},
    {"text": "About Us", "url": "/about.php"},
    {"text": "Properties", "url": "/property.php"},
    {"text": "Contact", "url": "/contact.php"},
    {"text": "Privacy Policy", "url": "/PrivacyPolicy.php"},
    {"text": "Terms & Conditions", "url": "/legal.php"}
]'),
('social_links', '[
    {"platform": "Facebook", "url": "https://facebook.com/apsrealestate", "icon": "fa-facebook"},
    {"platform": "Twitter", "url": "https://twitter.com/apsrealestate", "icon": "fa-twitter"},
    {"platform": "Instagram", "url": "https://instagram.com/apsrealestate", "icon": "fa-instagram"},
    {"platform": "LinkedIn", "url": "https://linkedin.com/company/apsrealestate", "icon": "fa-linkedin"}
]'),
('footer_content', 'Your trusted partner in real estate, providing quality properties and excellent service across India.');