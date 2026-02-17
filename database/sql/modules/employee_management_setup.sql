-- =============================================
-- APS Dream Home - Employee Management Tables
-- =============================================

-- Create roles table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `permissions` text,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default roles
INSERT IGNORE INTO `roles` (`name`, `level`, `permissions`, `status`) VALUES
('Super Admin', 10, '["admin", "employee", "associate", "customer", "property", "lead", "payment", "report", "settings", "backup"]', 'active'),
('Admin', 9, '["employee", "associate", "customer", "property", "lead", "payment", "report", "settings"]', 'active'),
('Manager', 8, '["associate", "customer", "property", "lead", "payment", "report"]', 'active'),
('Team Lead', 7, '["associate", "customer", "property", "lead"]', 'active'),
('Senior Associate', 6, '["customer", "property", "lead"]', 'active'),
('Associate', 5, '["customer", "property", "lead"]', 'active'),
('Junior Associate', 4, '["customer", "property"]', 'active'),
('Trainee', 3, '["customer"]', 'active'),
('Intern', 2, '["customer"]', 'active'),
('Support Staff', 1, '["customer"]', 'active');

-- Create departments table
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `head_employee_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `head_employee_id` (`head_employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default departments
INSERT IGNORE INTO `departments` (`name`, `description`, `status`) VALUES
('Management', 'Company leadership and strategic planning', 'active'),
('Sales & Marketing', 'Property sales, marketing, and customer acquisition', 'active'),
('Customer Service', 'Customer support and relationship management', 'active'),
('Operations', 'Property management and maintenance', 'active'),
('Finance & Accounts', 'Financial management and accounting', 'active'),
('HR & Admin', 'Human resources and administration', 'active'),
('IT & Systems', 'Technology infrastructure and support', 'active'),
('Legal & Compliance', 'Legal affairs and regulatory compliance', 'active'),
('Training & Development', 'Employee training and skill development', 'active'),
('Quality Assurance', 'Quality control and process improvement', 'active');

-- Create employees table
CREATE TABLE IF NOT EXISTS `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `employee_code` varchar(20) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `joining_date` date NOT NULL,
  `reporting_manager_id` int(11) DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `status` enum('active','inactive','terminated','resigned') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  KEY `role_id` (`role_id`),
  KEY `department_id` (`department_id`),
  KEY `reporting_manager_id` (`reporting_manager_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  FOREIGN KEY (`reporting_manager_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_activities table
CREATE TABLE IF NOT EXISTS `employee_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `performed_by` (`performed_by`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_tasks table
CREATE TABLE IF NOT EXISTS `employee_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `task_type_id` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled','on_hold') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `estimated_hours` decimal(4,2) DEFAULT NULL,
  `actual_hours` decimal(4,2) DEFAULT NULL,
  `completion_notes` text,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `assigned_by` (`assigned_by`),
  KEY `project_id` (`project_id`),
  KEY `task_type_id` (`task_type_id`),
  FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`employee_id`),
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_attendance table
CREATE TABLE IF NOT EXISTS `employee_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `check_in` timestamp NULL DEFAULT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `location` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_date` (`employee_id`, `check_in`),
  KEY `employee_id` (`employee_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create leave_types table
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `max_days` int(11) NOT NULL DEFAULT 30,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default leave types
INSERT IGNORE INTO `leave_types` (`name`, `description`, `max_days`, `requires_approval`) VALUES
('Casual Leave', 'Personal leave for personal matters', 12, 1),
('Sick Leave', 'Leave for medical reasons', 10, 1),
('Earned Leave', 'Leave earned through service', 15, 1),
('Maternity Leave', 'Leave for childbirth and childcare', 180, 1),
('Paternity Leave', 'Leave for new fathers', 15, 1),
('Emergency Leave', 'Unplanned leave for emergencies', 5, 0),
('Compensatory Off', 'Off day for working on holidays', 10, 1),
('Study Leave', 'Leave for educational purposes', 30, 1);

-- Create employee_leaves table
CREATE TABLE IF NOT EXISTS `employee_leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `applied_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `approved_by` (`approved_by`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create document_types table
CREATE TABLE IF NOT EXISTS `document_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default document types
INSERT IGNORE INTO `document_types` (`name`, `description`, `is_required`) VALUES
('Aadhaar Card', 'Government-issued identity proof', 1),
('PAN Card', 'Permanent Account Number for tax purposes', 1),
('Passport', 'International travel document', 0),
('Driving License', 'Motor vehicle driving license', 0),
('Voter ID', 'Election commission identity card', 0),
('Bank Passbook', 'Bank account details and transactions', 1),
('Educational Certificate', 'Highest educational qualification', 1),
('Experience Certificate', 'Previous employment proof', 0),
('Salary Slip', 'Current/previous salary proof', 0),
('Address Proof', 'Current residence proof', 1);

-- Create employee_documents table
CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_by` int(11) DEFAULT NULL,
  `verified_date` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `verified_by` (`verified_by`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_salary_history table
CREATE TABLE IF NOT EXISTS `employee_salary_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `previous_salary` decimal(10,2) DEFAULT NULL,
  `new_salary` decimal(10,2) NOT NULL,
  `effective_from` date NOT NULL,
  `reason` text,
  `approved_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `approved_by` (`approved_by`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_reviews table
CREATE TABLE IF NOT EXISTS `employee_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_period` varchar(50) NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `review_text` text,
  `goals_achieved` text,
  `areas_improvement` text,
  `next_goals` text,
  `overall_performance` enum('excellent','good','satisfactory','needs_improvement','poor') NOT NULL,
  `status` enum('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `reviewer_id` (`reviewer_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_training table
CREATE TABLE IF NOT EXISTS `employee_training` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `training_name` varchar(255) NOT NULL,
  `training_type` enum('internal','external','online','workshop','seminar') NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
  `completion_certificate` varchar(500) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_skills table
CREATE TABLE IF NOT EXISTS `employee_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner',
  `certification` varchar(255) DEFAULT NULL,
  `last_assessed` date DEFAULT NULL,
  `next_assessment_due` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create employee_certifications table
CREATE TABLE IF NOT EXISTS `employee_certifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `certification_type` varchar(255) NOT NULL,
  `certification_number` varchar(100) DEFAULT NULL,
  `issuing_authority` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','expired','revoked') NOT NULL DEFAULT 'active',
  `certificate_file` varchar(500) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create associate_invitations table (for customer to associate conversion)
CREATE TABLE IF NOT EXISTS `associate_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `sponsor_id` int(11) NOT NULL,
  `invitation_message` text,
  `status` enum('pending','accepted','rejected','expired') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `sponsor_id` (`sponsor_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sponsor_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create emi_calculator_history table
CREATE TABLE IF NOT EXISTS `emi_calculator_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `loan_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `loan_tenure` int(11) NOT NULL,
  `monthly_emi` decimal(15,2) NOT NULL,
  `total_interest` decimal(15,2) NOT NULL,
  `total_payment` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `property_id` (`property_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create project_task_types table
CREATE TABLE IF NOT EXISTS `project_task_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color_code` varchar(7) DEFAULT '#007bff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default project task types
INSERT IGNORE INTO `project_task_types` (`name`, `description`, `color_code`) VALUES
('Development', 'Software development tasks', '#007bff'),
('Design', 'UI/UX design tasks', '#28a745'),
('Testing', 'Quality assurance and testing', '#dc3545'),
('Documentation', 'Documentation and help content', '#6f42c1'),
('Marketing', 'Marketing and promotional activities', '#fd7e14'),
('Sales', 'Sales and customer acquisition', '#20c997'),
('Support', 'Customer support and helpdesk', '#6c757d'),
('Management', 'Project management and coordination', '#e83e8c'),
('Research', 'Research and analysis tasks', '#17a2b8'),
('Training', 'Training and skill development', '#ffc107');

-- Create projects table
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `project_manager_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `status` enum('planning','active','on_hold','completed','cancelled') NOT NULL DEFAULT 'planning',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `progress_percentage` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_manager_id` (`project_manager_id`),
  FOREIGN KEY (`project_manager_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add some indexes for better performance
ALTER TABLE `employees` ADD INDEX `idx_status_joining` (`status`, `joining_date`);
ALTER TABLE `employees` ADD INDEX `idx_department_role` (`department_id`, `role_id`);
ALTER TABLE `employees` ADD INDEX `idx_employee_code` (`employee_code`);

ALTER TABLE `employee_tasks` ADD INDEX `idx_assigned_to_status` (`assigned_to`, `status`);
ALTER TABLE `employee_tasks` ADD INDEX `idx_due_date_priority` (`due_date`, `priority`);

ALTER TABLE `employee_attendance` ADD INDEX `idx_employee_check_in` (`employee_id`, `check_in`);

ALTER TABLE `employee_leaves` ADD INDEX `idx_employee_status` (`employee_id`, `status`);

-- Create some initial sample data
INSERT IGNORE INTO `employees` (`user_id`, `employee_code`, `role_id`, `department_id`, `designation`, `salary`, `joining_date`, `status`) VALUES
(1, 'EMP001', 1, 1, 'Super Administrator', 100000.00, CURDATE(), 'active');

-- Update the employee with user_id = 1
UPDATE `employees` SET `created_at` = NOW(), `updated_at` = NOW() WHERE `user_id` = 1;
