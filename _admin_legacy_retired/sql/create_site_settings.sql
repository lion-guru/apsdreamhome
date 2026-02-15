CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL UNIQUE,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default header settings
INSERT IGNORE INTO site_settings (setting_name, value) VALUES
('header_menu_items', '[
    {"text": "Home", "url": "/", "icon": "fa-home"},
    {"text": "Properties", "url": "/property.php", "icon": "fa-building"},
    {"text": "About", "url": "/about.php", "icon": "fa-info-circle"},
    {"text": "Contact", "url": "/contact.php", "icon": "fa-envelope"}
]'),
('site_logo', 'assets/images/logo.png'),
('header_styles', '{
    "background": "#1e3c72",
    "text_color": "#ffffff"
}');