-- ==============================
-- APS DREAM HOME - COMPLETE DATABASE SCHEMA (PART 3)
-- Final Part with CMS, System & Advanced Features
-- ==============================

USE `apsdreamhome`;

-- ==============================
-- CONTENT MANAGEMENT SYSTEM
-- ==============================

-- 23. About Page
CREATE TABLE `about` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `title` varchar(100) NOT NULL,
    `content` longtext NOT NULL,
    `image` varchar(300) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. News & Updates
CREATE TABLE `news` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `date` date NOT NULL,
    `summary` text DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `content` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Team
CREATE TABLE `team` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `designation` varchar(100) DEFAULT NULL,
    `bio` text DEFAULT NULL,
    `photo` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Gallery
CREATE TABLE `gallery` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `image_path` varchar(255) NOT NULL,
    `caption` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Testimonials
CREATE TABLE `testimonials` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `client_name` varchar(100) NOT NULL,
    `testimonial` text NOT NULL,
    `client_photo` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- SYSTEM CONFIGURATION
-- ==============================

-- 28. Settings
CREATE TABLE `settings` (
    `key` varchar(100) NOT NULL,
    `value` text DEFAULT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. API Keys
CREATE TABLE `api_keys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `api_key` varchar(64) NOT NULL,
    `name` varchar(255) NOT NULL,
    `permissions` text DEFAULT NULL,
    `rate_limit` int(11) DEFAULT 100,
    `status` enum('active','revoked') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    `last_used_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- NOTIFICATION & LOGGING SYSTEM
-- ==============================

-- 30. Notifications
CREATE TABLE `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `type` varchar(50) DEFAULT 'info',
    `title` varchar(255) DEFAULT NULL,
    `message` text DEFAULT NULL,
    `link` varchar(255) DEFAULT NULL,
    `read_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31. Activity Logs
CREATE TABLE `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `action` varchar(100) NOT NULL,
    `table_name` varchar(100) DEFAULT NULL,
    `record_id` int(11) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `details` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `action` (`action`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. Audit Log
CREATE TABLE `audit_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) DEFAULT NULL,
    `details` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- FEEDBACK & SUPPORT
-- ==============================

-- 33. Feedback
CREATE TABLE `feedback` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `message` text DEFAULT NULL,
    `status` varchar(20) DEFAULT 'new',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34. Feedback Tickets (Enterprise)
CREATE TABLE `feedback_tickets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `message` text NOT NULL,
    `status` enum('open','closed') DEFAULT 'open',
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- REPORTING SYSTEM
-- ==============================

-- 35. Reports
CREATE TABLE `reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `type` varchar(50) NOT NULL,
    `content` text NOT NULL,
    `file_path` varchar(255) DEFAULT NULL,
    `generated_for_month` int(11) NOT NULL,
    `generated_for_year` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `type` (`type`),
    KEY `month_year` (`generated_for_month`, `generated_for_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- ADVANCED FEATURES
-- ==============================

-- 36. AI Chatbot Config
CREATE TABLE `ai_chatbot_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `provider` varchar(50) NOT NULL,
    `api_key` varchar(255) DEFAULT NULL,
    `webhook_url` varchar(255) DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 37. WhatsApp Automation Config
CREATE TABLE `whatsapp_automation_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `provider` varchar(50) NOT NULL,
    `api_key` varchar(255) DEFAULT NULL,
    `sender_number` varchar(50) DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 38. Marketing Campaigns
CREATE TABLE `marketing_campaigns` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `type` enum('email','sms') NOT NULL,
    `message` text NOT NULL,
    `scheduled_at` datetime DEFAULT NULL,
    `status` varchar(50) DEFAULT 'scheduled',
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 39. Customer Documents
CREATE TABLE `customer_documents` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NOT NULL,
    `doc_name` varchar(255) DEFAULT NULL,
    `doc_path` varchar(255) DEFAULT NULL,
    `status` varchar(50) DEFAULT 'uploaded',
    `uploaded_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 40. Payment Gateway Config
CREATE TABLE `payment_gateway_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `provider` varchar(50) NOT NULL,
    `api_key` varchar(255) DEFAULT NULL,
    `api_secret` varchar(255) DEFAULT NULL,
    `webhook_url` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 41. Foreclosure Logs
CREATE TABLE `foreclosure_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `emi_plan_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `foreclosure_amount` decimal(12,2) NOT NULL,
    `outstanding_amount` decimal(12,2) NOT NULL,
    `discount_amount` decimal(12,2) DEFAULT 0,
    `payment_id` int(11) DEFAULT NULL,
    `processed_by` int(11) NOT NULL,
    `processed_at` timestamp DEFAULT current_timestamp(),
    `remarks` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `emi_plan_id` (`emi_plan_id`),
    KEY `customer_id` (`customer_id`),
    KEY `payment_id` (`payment_id`),
    FOREIGN KEY (`emi_plan_id`) REFERENCES `emi_plans` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- SAMPLE DATA INSERTION
-- ==============================

-- Insert default admin users
INSERT INTO `admin` (`auser`, `apass`, `role`, `status`, `email`, `phone`) VALUES
('superadmin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'superadmin', 'active', 'superadmin@apsdreamhome.com', '9000000001'),
('admin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'admin', 'active', 'admin@apsdreamhome.com', '9000000002');

-- Insert default users
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `status`) VALUES
('Super Admin', 'superadmin@apsdreamhome.com', '9000000001', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'admin', 'active'),
('Demo User', 'user@demo.com', '9000000003', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'user', 'active');

-- Insert sample customers
INSERT INTO `customers` (`name`, `email`, `phone`, `address`, `status`) VALUES
('Rajesh Kumar', 'rajesh@example.com', '9876543210', '123 Main Street, Mumbai', 'active'),
('Priya Sharma', 'priya@example.com', '8765432109', '456 Park Avenue, Delhi', 'active');

-- Insert sample projects
INSERT INTO `projects` (`name`, `description`, `location`, `status`) VALUES
('Dream Valley', 'Premium residential project with modern amenities', 'Mumbai', 'active'),
('Green City', 'Eco-friendly housing project', 'Delhi', 'active'),
('Smart Homes', 'Technology integrated residential complex', 'Pune', 'active');

-- Insert sample properties
INSERT INTO `properties` (`title`, `description`, `price`, `location`, `bedrooms`, `bathrooms`, `area`, `status`) VALUES
('Luxury Villa', 'Beautiful 4BHK villa with modern amenities', 2500000.00, 'Mumbai', 4, 3, 2000.00, 'available'),
('2BHK Apartment', 'Cozy apartment in prime location', 1200000.00, 'Delhi', 2, 2, 1200.00, 'available'),
('3BHK Flat', 'Spacious flat with great view', 1800000.00, 'Pune', 3, 2, 1500.00, 'available');

-- Insert sample plots
INSERT INTO `plots` (`project_id`, `plot_no`, `size_sqft`, `current_price`, `status`) VALUES
(1, 'A-101', 2000.00, 1500000.00, 'available'),
(1, 'A-102', 1500.00, 1200000.00, 'available'),
(2, 'B-201', 1800.00, 1350000.00, 'available');

-- Insert sample associates with MLM structure
INSERT INTO `associates` (`name`, `email`, `phone`, `parent_id`, `level`, `commission_percent`, `join_date`, `status`, `total_business`, `direct_business`) VALUES
('Amit Patel', 'amit@associate.com', '9123456789', NULL, 1, 5.00, '2025-01-15', 'active', 500000.00, 100000.00),
('Sunita Singh', 'sunita@associate.com', '9234567890', 1, 2, 3.50, '2025-02-01', 'active', 450000.00, 350000.00),
('Vikram Joshi', 'vikram@associate.com', '9345678901', 1, 2, 3.50, '2025-02-15', 'active', 250000.00, 250000.00);

-- Insert default associate levels
INSERT INTO `associate_levels` (`level_name`, `level_number`, `min_team_size`, `min_personal_sales`, `commission_percentage`, `bonus_percentage`, `status`) VALUES
('Associate', 1, 0, 0, 5.00, 0.00, 'active'),
('Senior Associate', 2, 3, 500000, 7.00, 1.00, 'active'),
('Team Leader', 3, 10, 1500000, 10.00, 2.00, 'active'),
('Manager', 4, 25, 5000000, 12.00, 3.00, 'active');

-- Insert sample expenses for dashboard
INSERT INTO `expenses` (`category`, `description`, `amount`, `expense_date`, `status`, `created_by`) VALUES
('Office Rent', 'Monthly office rent payment', 50000.00, '2025-09-01', 'paid', 1),
('Marketing', 'Digital marketing campaign', 25000.00, '2025-09-05', 'paid', 1),
('Utilities', 'Electricity and water bills', 8000.00, '2025-09-10', 'paid', 1),
('Staff Salary', 'Monthly staff salaries', 150000.00, '2025-09-01', 'paid', 1),
('Travel', 'Business travel expenses', 12000.00, '2025-09-15', 'approved', 1);

-- Insert sample commission transactions for dashboard
INSERT INTO `commission_transactions` (`associate_id`, `commission_amount`, `transaction_type`, `status`) VALUES
(1, 125000.00, 'direct', 'paid'),
(2, 75000.00, 'direct', 'paid'),
(3, 50000.00, 'direct', 'pending'),
(1, 25000.00, 'level', 'paid'),
(2, 15000.00, 'level', 'paid');

-- Insert system settings
INSERT INTO `settings` (`key`, `value`) VALUES
('site_title', 'APS Dream Home'),
('site_description', 'Your trusted partner in real estate'),
('contact_email', 'info@apsdreamhome.com'),
('contact_phone', '+91-9000000001'),
('currency', 'INR'),
('currency_symbol', '₹'),
('maintenance_mode', '0'),
('email_verification_required', '1');

-- Insert about content
INSERT INTO `about` (`title`, `content`, `image`) VALUES
('About APS Dream Home', 'Welcome to APS Dream Home, your trusted partner in finding the perfect property. We are a leading real estate company specializing in residential and commercial properties across India.', 'about-image.jpg');

COMMIT;

-- ==============================
-- END OF APS DREAM HOME DATABASE SCHEMA
-- ==============================