INSERT INTO `about` (`id`, `title`, `content`, `image`) VALUES
(10, 'About Us', '...your existing content...', 'condos-pool.png');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_payments`
--

CREATE TABLE `accounting_payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_type` enum('received','paid') NOT NULL,
  `party_type` enum('customer','supplier','employee','bank','other') NOT NULL,
  `party_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','online','upi','card') NOT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_settings`
--

CREATE TABLE `accounting_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_settings`
--

INSERT INTO `accounting_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_system`, `updated_by`, `updated_at`) VALUES
(1, 'company_name', 'APS Dream Home', 'string', 'Company Name for Financial Reports', 0, NULL, '2025-09-24 21:22:27'),
(2, 'company_address', 'Company Address Here', 'string', 'Company Address for Reports', 0, NULL, '2025-09-24 21:22:27'),
(3, 'company_gstin', '', 'string', 'Company GST Number', 0, NULL, '2025-09-24 21:22:27'),
(4, 'company_pan', '', 'string', 'Company PAN Number', 0, NULL, '2025-09-24 21:22:27'),
(5, 'default_currency', 'INR', 'string', 'Default Currency Code', 1, NULL, '2025-09-24 21:22:27'),
(6, 'decimal_places', '2', 'integer', 'Decimal Places for Amounts', 1, NULL, '2025-09-24 21:22:27'),
(7, 'financial_year_start', '04-01', 'string', 'Financial Year Start (MM-DD)', 1, NULL, '2025-09-24 21:22:27'),
(8, 'gst_enabled', '1', 'boolean', 'Enable GST Calculations', 1, NULL, '2025-09-24 21:22:27'),
(9, 'auto_backup_enabled', '1', 'boolean', 'Enable Automatic Database Backup', 1, NULL, '2025-09-24 21:22:27'),
(10, 'invoice_prefix', 'INV', 'string', 'Invoice Number Prefix', 0, NULL, '2025-09-24 21:22:27'),
(11, 'expense_approval_required', '1', 'boolean', 'Require Approval for Expenses', 0, NULL, '2025-09-24 21:22:27'),
(12, 'bank_reconciliation_reminder', '7', 'integer', 'Bank Reconciliation Reminder Days', 0, NULL, '2025-09-24 21:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `auser` varchar(100) NOT NULL,
  `apass` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `auser`, `apass`, `role`, `status`, `email`, `phone`) VALUES
(1, 'superadmin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'superadmin', 'active', 'superadmin@demo.com', '9000000001'),
(29, 'admin', '$argon2id$v=19$m=65536,t=4,p=1$Vk9Iby9TdE94Sm9ka1pIRw$A9rt2FAHJ2beBxLwOqbzaHKCv3172jZQAPbL5mdPKXw', 'admin', 'active', 'admin1@aps.com', NULL),
(30, 'ceo', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'ceo', 'active', NULL, NULL),
(31, 'cfo', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'cfo', 'active', NULL, NULL),
(32, 'cm', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'cm', 'active', NULL, NULL),
(33, 'coo', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'coo', 'active', NULL, NULL),
(34, 'cto', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'cto', 'active', NULL, NULL),
(35, 'director', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'director', 'active', NULL, NULL),
(36, 'finance', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'finance', 'active', NULL, NULL),
(37, 'hr', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'hr', 'active', NULL, NULL),
(38, 'it_head', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'it_head', 'active', NULL, NULL),
(39, 'legal', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'legal', 'active', NULL, NULL),
(40, 'manager', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'manager', 'active', NULL, NULL),
(41, 'marketing', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'marketing', 'active', NULL, NULL),
(42, 'office_admin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'office_admin', 'active', NULL, NULL),
(43, 'official_employee', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'official_employee', 'active', NULL, NULL),
(44, 'operations', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'operations', 'active', NULL, NULL),
(45, 'sales', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'sales', 'active', NULL, NULL),
(46, 'super_admin', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'super_admin', 'active', NULL, NULL),
(47, 'support', '$argon2id$v=19$m=65536,t=4,p=1$b0hJS0R3NVB1Vmw1eC5IUg$RJ5bCZOJ5kdBUZtmaMoIma6XWBEwTmhu1i+lx3qvIIE', 'support', 'active', NULL, NULL),
(49, 'testadmin', '$argon2id$v=19$m=65536,t=4,p=1$LkxZYnB3bVIzb0R5ZHJqMg$X0JU0hxxfeNjPZ6JnfSdJdAhgVY3bgMlVZ0j70S4bJo', 'admin', 'active', 'test@admin.com', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `username`, `role`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'admin', 'admin', 'admin_login', 'Login success: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-04-24 18:03:16'),
(2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for username 1', 'Value for role 1', 'Value for action 1', 'Value for details 1', 'Delhi', 'Value for user_agent 1', '2025-04-30 18:30:00'),
(7, 2, 'Value for username 2', 'Value for role 2', 'Value for action 2', 'Value for details 2', 'Mumbai', 'Value for user_agent 2', '2025-05-06 18:30:00'),
(8, 3, 'Value for username 3', 'Value for role 3', 'Value for action 3', 'Value for details 3', 'Bangalore', 'Value for user_agent 3', '2025-05-12 18:30:00'),
(9, 4, 'Value for username 4', 'Value for role 4', 'Value for action 4', 'Value for details 4', 'Ahmedabad', 'Value for user_agent 4', '2025-05-18 18:30:00'),
(10, 5, 'Value for username 5', 'Value for role 5', 'Value for action 5', 'Value for details 5', 'Pune', 'Value for user_agent 5', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_chatbot_config`
--

CREATE TABLE `ai_chatbot_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chatbot_config`
--

INSERT INTO `ai_chatbot_config` (`id`, `provider`, `api_key`, `webhook_url`, `created_at`) VALUES
(1, '', NULL, NULL, '2025-05-17 18:03:21'),
(6, 'Value for provider 1', 'Value for api_key 1', 'Value for webhook_url 1', '2025-05-01 00:00:00'),
(7, 'Value for provider 2', 'Value for api_key 2', 'Value for webhook_url 2', '2025-05-07 00:00:00'),
(8, 'Value for provider 3', 'Value for api_key 3', 'Value for webhook_url 3', '2025-05-13 00:00:00'),
(9, 'Value for provider 4', 'Value for api_key 4', 'Value for webhook_url 4', '2025-05-19 00:00:00'),
(10, 'Value for provider 5', 'Value for api_key 5', 'Value for webhook_url 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_chatbot_interactions`
--

CREATE TABLE `ai_chatbot_interactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `satisfaction_score` decimal(2,1) DEFAULT NULL,
  `response_time` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chatbot_interactions`
--

INSERT INTO `ai_chatbot_interactions` (`id`, `user_id`, `query`, `response`, `satisfaction_score`, `response_time`, `created_at`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for query 1', 'Value for response 1', 9.9, 999.99, '2025-04-30 18:30:00'),
(7, 2, 'Value for query 2', 'Value for response 2', 9.9, 999.99, '2025-05-06 18:30:00'),
(8, 3, 'Value for query 3', 'Value for response 3', 9.9, 999.99, '2025-05-12 18:30:00'),
(9, 4, 'Value for query 4', 'Value for response 4', 9.9, 999.99, '2025-05-18 18:30:00'),
(10, 5, 'Value for query 5', 'Value for response 5', 9.9, 999.99, '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_config`
--

CREATE TABLE `ai_config` (
  `id` int(11) NOT NULL,
  `feature` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `config_json` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_config`
--

INSERT INTO `ai_config` (`id`, `feature`, `enabled`, `config_json`, `updated_by`, `updated_at`) VALUES
(1, NULL, 1, NULL, NULL, '2025-05-17 12:33:21'),
(6, 'Value for feature 1', 1, 'Value for config_json 1', 1, '2025-04-30 18:30:00'),
(7, 'Value for feature 2', 2, 'Value for config_json 2', 2, '2025-05-06 18:30:00'),
(8, 'Value for feature 3', 3, 'Value for config_json 3', 3, '2025-05-12 18:30:00'),
(9, 'Value for feature 4', 4, 'Value for config_json 4', 4, '2025-05-18 18:30:00'),
(10, 'Value for feature 5', 5, 'Value for config_json 5', 5, '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_lead_scores`
--

CREATE TABLE `ai_lead_scores` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `scored_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_lead_scores`
--

INSERT INTO `ai_lead_scores` (`id`, `lead_id`, `score`, `scored_at`) VALUES
(1, 0, 0, '2025-05-17 18:03:21'),
(6, 1, 1, '2025-05-01 00:00:00'),
(7, 2, 2, '2025-05-07 00:00:00'),
(8, 3, 3, '2025-05-13 00:00:00'),
(9, 4, 4, '2025-05-19 00:00:00'),
(10, 5, 5, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ai_logs`
--

CREATE TABLE `ai_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `input_text` text DEFAULT NULL,
  `ai_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_logs`
--

INSERT INTO `ai_logs` (`id`, `user_id`, `action`, `input_text`, `ai_response`, `created_at`) VALUES
(1, 1, 'chat', 'What is the price of Plot 101?', 'The price is 15,00,000 INR.', '2025-04-21 20:54:15'),
(2, 2, 'chat', 'Show my bookings.', 'You have 1 booking for Plot 101.', '2025-04-21 20:54:15'),
(3, NULL, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for action 1', 'This is a sample message for record 1.', 'Value for ai_response 1', '2025-04-30 18:30:00'),
(7, 2, 'Value for action 2', 'This is a sample message for record 2.', 'Value for ai_response 2', '2025-05-06 18:30:00'),
(8, 3, 'Value for action 3', 'This is a sample message for record 3.', 'Value for ai_response 3', '2025-05-12 18:30:00'),
(9, 4, 'Value for action 4', 'This is a sample message for record 4.', 'Value for ai_response 4', '2025-05-18 18:30:00'),
(10, 5, 'Value for action 5', 'This is a sample message for record 5.', 'Value for ai_response 5', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_developers`
--

CREATE TABLE `api_developers` (
  `id` int(11) NOT NULL,
  `dev_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_developers`
--

INSERT INTO `api_developers` (`id`, `dev_name`, `email`, `api_key`, `status`, `created_at`) VALUES
(1, '', '', '', 'active', '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'rahul@example.com', 'Value for api_key 1', 'active', '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'priya@example.com', 'Value for api_key 2', 'pending', '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'amit@example.com', 'Value for api_key 3', 'completed', '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'neha@example.com', 'Value for api_key 4', 'cancelled', '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'vikram@example.com', 'Value for api_key 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_integrations`
--

CREATE TABLE `api_integrations` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `api_url` varchar(255) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_integrations`
--

INSERT INTO `api_integrations` (`id`, `service_name`, `api_url`, `api_key`, `status`, `created_at`) VALUES
(1, '', '', NULL, 'active', '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for api_url 1', 'Value for api_key 1', 'active', '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for api_url 2', 'Value for api_key 2', 'pending', '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'Value for api_url 3', 'Value for api_key 3', 'completed', '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'Value for api_url 4', 'Value for api_key 4', 'cancelled', '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'Value for api_url 5', 'Value for api_key 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `permissions` text DEFAULT NULL,
  `rate_limit` int(11) DEFAULT 100,
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `last_used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `user_id`, `api_key`, `name`, `permissions`, `rate_limit`, `status`, `created_at`, `updated_at`, `last_used_at`) VALUES
(1, 1, '7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', 'Admin API Key', '[\"*\"]', 1000, 'active', '2025-05-25 10:52:15', NULL, NULL),
(2, 1, '0a7f3f913e97202519116e266ba4f8f9d22e35afd76c34c06af6ce64c38db88b', 'CLI Test Key', '\"[\\\"*\\\"]\"', 100, 'active', '2025-10-06 19:39:01', NULL, NULL),
(3, 1, 'b1a97e395722d878110267a2954c237d224f9182cb56f207f3b49144b369ce0b', 'CLI Test Key', '\"[\\\"*\\\"]\"', 100, 'active', '2025-10-06 19:39:43', NULL, NULL),
(4, 1, '1e4cad08597feb2c51fd827ddb87394830f32b3ae4df5fe18b9a2befa658a96c', 'CLI Test Key', '\"[\\\"*\\\"]\"', 100, 'active', '2025-10-06 19:40:40', NULL, NULL),
(5, 1, '86c0d10d5ac7ccaf94a5fd2923d32869508cf83e126cafd57817474bb4799dfd', 'CLI Test Key', '[\"*\"]', 100, 'active', '2025-10-06 19:41:25', NULL, NULL),
(6, 1, 'b848379bedc8c42e2b3c8aac88c85ac5a391d956af94578982364999c59c96d0', 'CLI Test Key', '[\"*\"]', 100, 'active', '2025-10-06 20:25:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `api_rate_limits`
--

CREATE TABLE `api_rate_limits` (
  `id` int(11) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_request_logs`
--

CREATE TABLE `api_request_logs` (
  `id` int(11) NOT NULL,
  `api_key_id` int(11) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_request_logs`
--

INSERT INTO `api_request_logs` (`id`, `api_key_id`, `endpoint`, `request_time`, `ip_address`, `user_agent`) VALUES
(1, 1, '/apsdreamhomefinal/api/v1/test.php?api_key=7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', '2025-05-25 10:56:38', '::1', 'curl/8.9.1'),
(2, 1, '/apsdreamhomefinal/api/v1/test.php?api_key=7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', '2025-05-25 10:57:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'),
(3, 5, '/test/endpoint', '2025-10-06 19:41:25', '127.0.0.1', 'CLI-Test-Script'),
(4, 5, '/api/test_api_auth', '2025-10-06 19:48:17', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6328'),
(5, 6, '/test/endpoint', '2025-10-06 20:25:35', '127.0.0.1', 'CLI-Test-Script'),
(6, 6, '/api/test_api_auth', '2025-10-06 20:26:08', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6328');

-- --------------------------------------------------------

--
-- Table structure for table `api_sandbox`
--

CREATE TABLE `api_sandbox` (
  `id` int(11) NOT NULL,
  `dev_name` varchar(255) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_sandbox`
--

INSERT INTO `api_sandbox` (`id`, `dev_name`, `endpoint`, `payload`, `status`, `created_at`) VALUES
(1, NULL, NULL, NULL, 'pending', '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for endpoint 1', 'Value for payload 1', 'active', '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for endpoint 2', 'Value for payload 2', 'pending', '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'Value for endpoint 3', 'Value for payload 3', 'completed', '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'Value for endpoint 4', 'Value for payload 4', 'cancelled', '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'Value for endpoint 5', 'Value for payload 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `api_usage`
--

CREATE TABLE `api_usage` (
  `id` int(11) NOT NULL,
  `dev_name` varchar(255) DEFAULT NULL,
  `api_key` varchar(64) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 1,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_usage`
--

INSERT INTO `api_usage` (`id`, `dev_name`, `api_key`, `endpoint`, `usage_count`, `timestamp`) VALUES
(1, NULL, NULL, NULL, 1, '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for api_key 1', 'Value for endpoint 1', 1, '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for api_key 2', 'Value for endpoint 2', 2, '2025-05-02 00:00:00'),
(8, 'Amit Kumar', 'Value for api_key 3', 'Value for endpoint 3', 3, '2025-05-03 00:00:00'),
(9, 'Neha Patel', 'Value for api_key 4', 'Value for endpoint 4', 4, '2025-05-04 00:00:00'),
(10, 'Vikram Mehta', 'Value for api_key 5', 'Value for endpoint 5', 5, '2025-05-05 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `app_store`
--

CREATE TABLE `app_store` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `app_url` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_store`
--

INSERT INTO `app_store` (`id`, `app_name`, `provider`, `app_url`, `price`, `created_at`) VALUES
(1, '', NULL, NULL, 0.00, '2025-05-17 18:03:21'),
(6, 'Rahul Sharma', 'Value for provider 1', 'Value for app_url 1', 15000000.00, '2025-05-01 00:00:00'),
(7, 'Priya Singh', 'Value for provider 2', 'Value for app_url 2', 7000000.00, '2025-05-07 00:00:00'),
(8, 'Amit Kumar', 'Value for provider 3', 'Value for app_url 3', 9000000.00, '2025-05-13 00:00:00'),
(9, 'Neha Patel', 'Value for provider 4', 'Value for app_url 4', 20000000.00, '2025-05-19 00:00:00'),
(10, 'Vikram Mehta', 'Value for provider 5', 'Value for app_url 5', 25000000.00, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ar_vr_tours`
--

CREATE TABLE `ar_vr_tours` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `asset_url` varchar(255) NOT NULL,
  `asset_type` varchar(50) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ar_vr_tours`
--

INSERT INTO `ar_vr_tours` (`id`, `property_id`, `asset_url`, `asset_type`, `uploaded_at`) VALUES
(1, 0, '', NULL, '2025-05-17 18:03:21'),
(6, 1, 'Value for asset_url 1', 'villa', '2025-05-01 00:00:00'),
(7, 2, 'Value for asset_url 2', 'apartment', '2025-05-07 00:00:00'),
(8, 3, 'Value for asset_url 3', 'house', '2025-05-13 00:00:00'),
(9, 4, 'Value for asset_url 4', 'villa', '2025-05-19 00:00:00'),
(10, 5, 'Value for asset_url 5', 'penthouse', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `associates`
--

CREATE TABLE `associates` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT 5.00,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT 1,
  `total_business` decimal(15,2) DEFAULT 0.00,
  `total_earnings` decimal(15,2) DEFAULT 0.00,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive','suspended','terminated') DEFAULT 'active',
  `kyc_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associates`
--

INSERT INTO `associates` (`id`, `parent_id`, `name`, `email`, `phone`, `mobile`, `commission_rate`, `address`, `city`, `state`, `pincode`, `aadhar_number`, `pan_number`, `bank_account`, `ifsc_code`, `sponsor_id`, `level_id`, `total_business`, `total_earnings`, `join_date`, `status`, `kyc_status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Rahul Sharma', 'rahul.sharma@example.com', '9876543210', NULL, 15.00, '123 MG Road', 'Mumbai', 'Maharashtra', '400001', '123412341234', 'ABCDE1234F', '12345678901234', 'HDFC0001234', NULL, 3, 2550000.00, 382500.00, NULL, 'active', 'pending', '2024-01-15 04:30:00', '2025-09-24 22:29:20'),
(2, NULL, 'Priya Patel', 'priya.patel@example.com', '9876543211', NULL, 10.00, '456 Linking Road', 'Mumbai', 'Maharashtra', '400052', '234523452345', 'BCDEF2345G', '23456789012345', 'ICIC0001234', 1, 4, 6300000.00, 630000.00, NULL, 'active', 'pending', '2024-02-01 06:00:00', '2025-09-24 22:29:20'),
(3, NULL, 'Amit Singh', 'amit.singh@example.com', '9876543212', NULL, 12.50, '789 Andheri East', 'Mumbai', 'Maharashtra', '400069', '345634563456', 'CDEFG3456H', '34567890123456', 'SBIN0001234', 1, 5, 20000000.00, 2500000.00, NULL, 'active', 'pending', '2024-02-05 08:45:00', '2025-09-24 22:29:20'),
(4, NULL, 'Neha Gupta', 'neha.gupta@example.com', '9876543213', NULL, 7.00, '321 Bandra West', 'Mumbai', 'Maharashtra', '400050', '456745674567', 'DEFGH4567I', '45678901234567', 'HDFC0002345', 2, 1, 0.00, 0.00, NULL, 'active', 'pending', '2024-02-15 04:15:00', '2025-09-24 22:29:20'),
(5, NULL, 'Vikram Joshi', 'vikram.joshi@example.com', '9876543214', NULL, 5.00, '654 Juhu', 'Mumbai', 'Maharashtra', '400049', '567856785678', 'EFGHI5678J', '56789012345678', 'ICIC0002345', 3, 1, 0.00, 0.00, NULL, 'active', 'pending', '2024-02-20 11:00:00', NULL),
(6, NULL, 'Ananya Reddy', 'ananya.reddy@example.com', '9876543215', NULL, 5.00, '987 Powai', 'Mumbai', 'Maharashtra', '400076', '678967896789', 'FGHIJ6789K', '67890123456789', 'SBIN0002345', 2, 1, 0.00, 0.00, NULL, 'inactive', 'pending', '2024-02-25 07:50:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `associate_levels`
--

CREATE TABLE `associate_levels` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `commission_percent` decimal(5,2) NOT NULL,
  `direct_referral_bonus` decimal(5,2) DEFAULT 0.00,
  `level_bonus` decimal(5,2) DEFAULT 0.00,
  `reward_description` text DEFAULT NULL,
  `min_team_size` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `min_business` decimal(15,2) NOT NULL DEFAULT 0.00,
  `max_business` decimal(15,2) NOT NULL DEFAULT 99999999.99
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associate_levels`
--

INSERT INTO `associate_levels` (`id`, `name`, `commission_percent`, `direct_referral_bonus`, `level_bonus`, `reward_description`, `min_team_size`, `status`, `created_at`, `updated_at`, `min_business`, `max_business`) VALUES
(1, 'Starter', 5.00, 1.00, 0.00, 'Basic level for new associates', 0, 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20', 0.00, 500000.00),
(2, 'Bronze', 7.00, 1.50, 0.50, 'Bronze level with increased commission', 3, 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20', 500001.00, 2000000.00),
(3, 'Silver', 10.00, 2.00, 1.00, 'Silver level with higher rewards', 10, 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20', 2000001.00, 5000000.00),
(4, 'Gold', 12.50, 2.50, 1.50, 'Gold level with premium benefits', 25, 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20', 5000001.00, 10000000.00),
(5, 'Platinum', 15.00, 3.00, 2.00, 'Top level with maximum benefits', 50, 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20', 10000001.00, 999999999.00);

-- --------------------------------------------------------

--
-- Table structure for table `associate_mlm`
--

CREATE TABLE `associate_mlm` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `in_time` time DEFAULT NULL,
  `out_time` time DEFAULT NULL,
  `status` enum('present','absent','leave') DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `in_time`, `out_time`, `status`) VALUES
(1, 10, '2025-04-01', '09:00:00', '18:00:00', 'present'),
(2, 11, '2025-04-01', '09:15:00', '18:10:00', 'present'),
(3, NULL, NULL, NULL, NULL, 'present'),
(6, 1, '2025-05-01', '10:01:00', '10:01:00', ''),
(7, 2, '2025-05-07', '10:02:00', '10:02:00', ''),
(8, 3, '2025-05-13', '10:03:00', '10:03:00', ''),
(9, 4, '2025-05-19', '10:04:00', '10:04:00', ''),
(10, 5, '2025-05-25', '10:05:00', '10:05:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `audit_access_log`
--

CREATE TABLE `audit_access_log` (
  `id` int(11) NOT NULL,
  `accessed_at` datetime DEFAULT current_timestamp(),
  `action` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_access_log`
--

INSERT INTO `audit_access_log` (`id`, `accessed_at`, `action`, `user_id`, `details`) VALUES
(1, '2025-05-17 18:03:21', NULL, NULL, NULL),
(6, '2025-05-01 00:00:00', 'Value for action 1', 1, 'Value for details 1'),
(7, '2025-05-07 00:00:00', 'Value for action 2', 2, 'Value for details 2'),
(8, '2025-05-13 00:00:00', 'Value for action 3', 3, 'Value for details 3'),
(9, '2025-05-19 00:00:00', 'Value for action 4', 4, 'Value for details 4'),
(10, '2025-05-25 00:00:00', 'Value for action 5', 5, 'Value for details 5');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'login', 'Super Admin logged in.', '2025-04-21 20:54:15'),
(2, 2, 'booking', 'Agent One booked Plot 101 for Customer One.', '2025-04-21 20:54:15'),
(3, NULL, NULL, NULL, '2025-05-17 12:33:21'),
(6, 1, 'Value for action 1', 'Value for details 1', '2025-04-30 18:30:00'),
(7, 2, 'Value for action 2', 'Value for details 2', '2025-05-06 18:30:00'),
(8, 3, 'Value for action 3', 'Value for details 3', '2025-05-12 18:30:00'),
(9, 4, 'Value for action 4', 'Value for details 4', '2025-05-18 18:30:00'),
(10, 5, 'Value for action 5', 'Value for details 5', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` int(11) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `changed_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changed_fields`)),
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `ifsc_code` varchar(15) NOT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `account_type` enum('savings','current','business','fd','loan') DEFAULT 'current',
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `minimum_balance` decimal(15,2) DEFAULT 0.00,
  `overdraft_limit` decimal(15,2) DEFAULT 0.00,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_reconciliation`
--

CREATE TABLE `bank_reconciliation` (
  `id` int(11) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `reconciliation_date` date NOT NULL,
  `book_balance` decimal(15,2) NOT NULL,
  `bank_balance` decimal(15,2) NOT NULL,
  `difference_amount` decimal(15,2) NOT NULL,
  `unreconciled_transactions` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `reconciled_by` int(11) NOT NULL,
  `status` enum('in_progress','completed','reviewed') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` enum('debit','credit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `cheque_number` varchar(50) DEFAULT NULL,
  `party_name` varchar(255) DEFAULT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `is_reconciled` tinyint(1) DEFAULT 0,
  `reconciled_date` date DEFAULT NULL,
  `bank_statement_ref` varchar(100) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_number` varchar(50) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked',
  `source` enum('direct','associate','online','agent') DEFAULT 'direct',
  `remarks` text DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `created_by` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `payment_plan` enum('full_payment','installment','emi') DEFAULT 'full_payment',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_number`, `property_id`, `plot_id`, `customer_id`, `associate_id`, `booking_date`, `status`, `source`, `remarks`, `documents`, `created_by`, `amount`, `total_amount`, `payment_plan`, `created_at`, `updated_at`) VALUES
(1, 'BK00001', 2, NULL, 1, 2, '2024-01-08', '', 'online', NULL, NULL, NULL, 4500000.00, 45000000.00, 'installment', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(2, 'BK00001', 5, NULL, 1, 2, '2024-01-08', 'completed', 'online', NULL, NULL, NULL, 1800000.00, 18000000.00, 'installment', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(3, 'BK00005', 1, NULL, 5, 3, '2024-02-05', '', 'direct', NULL, NULL, NULL, 2500000.00, 25000000.00, 'full_payment', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(4, 'BK00005', 4, NULL, 5, 3, '2024-02-05', '', 'direct', NULL, NULL, NULL, 7500000.00, 75000000.00, 'full_payment', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(5, 'BK00009', 3, NULL, 9, 1, '2024-03-04', '', 'associate', NULL, NULL, NULL, 850000.00, 8500000.00, 'emi', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(6, 'BK00012', 3, NULL, 12, 1, '2024-03-25', 'cancelled', 'associate', NULL, NULL, NULL, 850000.00, 8500000.00, 'emi', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(7, 'BK00002', 1, NULL, 2, 3, '2024-01-15', '', 'direct', NULL, NULL, NULL, 2500000.00, 25000000.00, 'full_payment', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(8, 'BK00002', 4, NULL, 2, 3, '2024-01-15', '', 'direct', NULL, NULL, NULL, 7500000.00, 75000000.00, 'full_payment', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(9, 'BK00003', 3, NULL, 3, 1, '2024-01-22', '', 'associate', NULL, NULL, NULL, 850000.00, 8500000.00, 'emi', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(10, 'BK00006', 3, NULL, 6, 1, '2024-02-12', '', 'associate', NULL, NULL, NULL, 850000.00, 8500000.00, 'emi', '2025-09-24 22:29:20', '2025-09-24 22:29:20');

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `update_associate_business` AFTER INSERT ON `bookings` FOR EACH ROW BEGIN
    IF NEW.associate_id IS NOT NULL AND NEW.status = 'confirmed' THEN
        UPDATE associates
        SET total_business = total_business + NEW.amount
        WHERE id = NEW.associate_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `booking_summary`
-- (See below for the actual view)
--
CREATE TABLE `booking_summary` (
`booking_id` int(11)
,`booking_number` varchar(50)
,`booking_date` date
,`customer_id` int(11)
,`customer_name` varchar(100)
,`customer_phone` varchar(20)
,`property_id` int(11)
,`property_title` varchar(255)
,`property_address` mediumtext
,`property_price` decimal(15,2)
,`booking_amount` decimal(15,2)
,`booking_status` enum('booked','cancelled','completed')
,`associate_id` int(11)
,`associate_name` varchar(100)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `budget_planning`
--

CREATE TABLE `budget_planning` (
  `id` int(11) NOT NULL,
  `budget_name` varchar(255) NOT NULL,
  `budget_year` year(4) NOT NULL,
  `budget_type` enum('annual','quarterly','monthly') NOT NULL,
  `account_id` int(11) NOT NULL,
  `budgeted_amount` decimal(15,2) NOT NULL,
  `actual_amount` decimal(15,2) DEFAULT 0.00,
  `variance_amount` decimal(15,2) DEFAULT 0.00,
  `variance_percentage` decimal(5,2) DEFAULT 0.00,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `builders`
--

CREATE TABLE `builders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `specialization` enum('residential','commercial','industrial','infrastructure') DEFAULT 'residential',
  `rating` decimal(2,1) DEFAULT 5.0,
  `total_projects` int(11) DEFAULT 0,
  `completed_projects` int(11) DEFAULT 0,
  `ongoing_projects` int(11) DEFAULT 0,
  `status` enum('active','inactive','blacklisted') DEFAULT 'active',
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `gst_number` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `builder_payments`
--

CREATE TABLE `builder_payments` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `builder_id` int(11) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `payment_type` enum('advance','milestone','final','penalty','bonus') DEFAULT 'milestone',
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','online') DEFAULT 'bank_transfer',
  `transaction_id` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `milestone_reference` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `net_amount` decimal(15,2) DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_flow_projections`
--

CREATE TABLE `cash_flow_projections` (
  `id` int(11) NOT NULL,
  `projection_date` date NOT NULL,
  `projected_inflow` decimal(15,2) NOT NULL,
  `projected_outflow` decimal(15,2) NOT NULL,
  `net_cash_flow` decimal(15,2) NOT NULL,
  `cumulative_balance` decimal(15,2) NOT NULL,
  `actual_inflow` decimal(15,2) DEFAULT NULL,
  `actual_outflow` decimal(15,2) DEFAULT NULL,
  `actual_net_flow` decimal(15,2) DEFAULT NULL,
  `variance_inflow` decimal(15,2) DEFAULT NULL,
  `variance_outflow` decimal(15,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` enum('asset','liability','equity','income','expense') NOT NULL,
  `account_category` enum('current_asset','fixed_asset','current_liability','long_term_liability','owner_equity','revenue','operating_expense','non_operating_expense') NOT NULL,
  `parent_account_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chart_of_accounts`
--

INSERT INTO `chart_of_accounts` (`id`, `account_code`, `account_name`, `account_type`, `account_category`, `parent_account_id`, `is_active`, `opening_balance`, `current_balance`, `description`, `created_at`, `updated_at`) VALUES
(1, '1000', 'ASSETS', 'asset', 'current_asset', NULL, 1, 0.00, 0.00, 'All Asset Accounts', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(2, '1100', 'Current Assets', 'asset', 'current_asset', 1, 1, 0.00, 0.00, 'Current Assets Group', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(3, '1110', 'Cash in Hand', 'asset', 'current_asset', 2, 1, 0.00, 0.00, 'Physical Cash', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(4, '1120', 'Bank Accounts', 'asset', 'current_asset', 2, 1, 0.00, 0.00, 'All Bank Accounts', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(5, '1130', 'Accounts Receivable', 'asset', 'current_asset', 2, 1, 0.00, 0.00, 'Customer Outstanding', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(6, '1140', 'Inventory', 'asset', 'current_asset', 2, 1, 0.00, 0.00, 'Stock/Inventory', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(7, '1200', 'Fixed Assets', 'asset', 'fixed_asset', 1, 1, 0.00, 0.00, 'Fixed Assets Group', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(8, '1210', 'Land and Building', 'asset', 'fixed_asset', 7, 1, 0.00, 0.00, 'Property Assets', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(9, '1220', 'Furniture and Fixtures', 'asset', 'fixed_asset', 7, 1, 0.00, 0.00, 'Office Furniture', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(10, '1230', 'Computer Equipment', 'asset', 'fixed_asset', 7, 1, 0.00, 0.00, 'IT Equipment', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(11, '2000', 'LIABILITIES', 'liability', 'current_liability', NULL, 1, 0.00, 0.00, 'All Liability Accounts', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(12, '2100', 'Current Liabilities', 'liability', 'current_liability', 11, 1, 0.00, 0.00, 'Current Liabilities Group', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(13, '2110', 'Accounts Payable', 'liability', 'current_liability', 12, 1, 0.00, 0.00, 'Supplier Outstanding', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(14, '2120', 'Short Term Loans', 'liability', 'current_liability', 12, 1, 0.00, 0.00, 'Short Term Borrowings', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(15, '2130', 'Tax Payable', 'liability', 'current_liability', 12, 1, 0.00, 0.00, 'Tax Obligations', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(16, '2200', 'Long Term Liabilities', 'liability', 'long_term_liability', 11, 1, 0.00, 0.00, 'Long Term Liabilities', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(17, '2210', 'Long Term Loans', 'liability', 'long_term_liability', 16, 1, 0.00, 0.00, 'Long Term Borrowings', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(18, '3000', 'EQUITY', 'equity', 'owner_equity', NULL, 1, 0.00, 0.00, 'Owner Equity Accounts', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(19, '3100', 'Capital', 'equity', 'owner_equity', 18, 1, 0.00, 0.00, 'Owner Capital', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(20, '3200', 'Retained Earnings', 'equity', 'owner_equity', 18, 1, 0.00, 0.00, 'Accumulated Profits', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(21, '4000', 'INCOME', 'income', 'revenue', NULL, 1, 0.00, 0.00, 'All Income Accounts', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(22, '4100', 'Sales Revenue', 'income', 'revenue', 21, 1, 0.00, 0.00, 'Primary Sales Income', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(23, '4200', 'Other Income', 'income', 'revenue', 21, 1, 0.00, 0.00, 'Other Revenue Sources', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(24, '5000', 'EXPENSES', 'expense', 'operating_expense', NULL, 1, 0.00, 0.00, 'All Expense Accounts', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(25, '5100', 'Operating Expenses', 'expense', 'operating_expense', 24, 1, 0.00, 0.00, 'Regular Operating Costs', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(26, '5110', 'Office Rent', 'expense', 'operating_expense', 25, 1, 0.00, 0.00, 'Office Rental Expenses', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(27, '5120', 'Salaries and Wages', 'expense', 'operating_expense', 25, 1, 0.00, 0.00, 'Employee Compensation', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(28, '5130', 'Utilities', 'expense', 'operating_expense', 25, 1, 0.00, 0.00, 'Electricity, Water, etc', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(29, '5140', 'Marketing Expenses', 'expense', 'operating_expense', 25, 1, 0.00, 0.00, 'Advertising and Promotion', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(30, '5200', 'Administrative Expenses', 'expense', 'operating_expense', 24, 1, 0.00, 0.00, 'Admin and General Expenses', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(31, '5210', 'Professional Fees', 'expense', 'operating_expense', 30, 1, 0.00, 0.00, 'Legal, Audit, Consulting', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(32, '5220', 'Insurance', 'expense', 'operating_expense', 30, 1, 0.00, 0.00, 'Insurance Premiums', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(33, '5300', 'Financial Expenses', 'expense', 'non_operating_expense', 24, 1, 0.00, 0.00, 'Interest and Financial Costs', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(34, '5310', 'Interest Expense', 'expense', 'non_operating_expense', 33, 1, 0.00, 0.00, 'Loan Interest Payments', '2025-09-24 21:22:27', '2025-09-24 21:22:27'),
(35, '5320', 'Bank Charges', 'expense', 'non_operating_expense', 33, 1, 0.00, 0.00, 'Banking Fees and Charges', '2025-09-24 21:22:27', '2025-09-24 21:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_conversations`
--

CREATE TABLE `chatbot_conversations` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_email`, `message`, `created_at`) VALUES
(1, NULL, NULL, '2025-05-17 18:03:21'),
(6, 'rahul@example.com', 'This is a sample message for record 1.', '2025-05-01 00:00:00'),
(7, 'priya@example.com', 'This is a sample message for record 2.', '2025-05-07 00:00:00'),
(8, 'amit@example.com', 'This is a sample message for record 3.', '2025-05-13 00:00:00'),
(9, 'neha@example.com', 'This is a sample message for record 4.', '2025-05-19 00:00:00'),
(10, 'vikram@example.com', 'This is a sample message for record 5.', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `colonies`
--

CREATE TABLE `colonies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `total_area` varchar(50) DEFAULT NULL,
  `developed_area` varchar(50) DEFAULT NULL,
  `total_plots` int(11) DEFAULT 0,
  `available_plots` int(11) DEFAULT 0,
  `completion_status` enum('Planning','Under Development','Completed') DEFAULT 'Planning',
  `status` enum('available','sold_out','coming_soon') DEFAULT 'available',
  `starting_price` decimal(15,2) DEFAULT 0.00,
  `current_price` decimal(15,2) DEFAULT 0.00,
  `features` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `coordinates` text DEFAULT NULL,
  `developer` varchar(255) DEFAULT 'APS Dream Homes Private Limited',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colonies`
--

INSERT INTO `colonies` (`id`, `name`, `location`, `description`, `total_area`, `developed_area`, `total_plots`, `available_plots`, `completion_status`, `status`, `starting_price`, `current_price`, `features`, `amenities`, `coordinates`, `developer`, `created_at`, `updated_at`) VALUES
(1, 'Suryoday Colony', 'Gorakhpur, Uttar Pradesh', 'Premium residential colony developed by APS Dream Homes in Gorakhpur', '25 Acres', '25 Acres', 200, 0, 'Completed', 'sold_out', 1200000.00, 1500000.00, '24/7 Security,Wide Roads,Green Spaces,Community Hall,Children Play Area', 'Power Backup,Water Supply,Sewage System,Street Lights,Landscaped Gardens', '{\"latitude\": 26.7606, \"longitude\": 83.3732}', 'APS Dream Homes Private Limited', '2025-09-30 18:09:29', '2025-09-30 18:09:29'),
(2, 'Raghunath Nagari', 'Gorakhpur, Uttar Pradesh', 'Luxury residential project with modern amenities', '15 Acres', '15 Acres', 150, 0, 'Completed', 'sold_out', 1500000.00, 1800000.00, 'Gated Community,24/7 Security,Club House,Swimming Pool,Jogging Track', 'Power Backup,Water Supply,Underground Electricity,Landscaped Gardens', '{\"latitude\": 26.7445, \"longitude\": 83.4032}', 'APS Dream Homes Private Limited', '2025-09-30 18:09:29', '2025-09-30 18:09:29'),
(3, 'Brajradha Nagri', 'Gorakhpur, Uttar Pradesh', 'Affordable housing with all basic amenities', '20 Acres', '20 Acres', 180, 0, 'Completed', 'sold_out', 1000000.00, 1300000.00, '24/7 Security,Park,Community Center,Children Play Area', 'Water Supply,Electricity,Street Lights', '{\"latitude\": 26.7523, \"longitude\": 83.3921}', 'APS Dream Homes Private Limited', '2025-09-30 18:09:29', '2025-09-30 18:09:29'),
(4, 'Stuti Bihar', 'Sonbarsa, Gorakhpur', 'Peaceful living in the lap of nature', '30 Acres', '30 Acres', 250, 0, 'Completed', 'sold_out', 800000.00, 1100000.00, 'Green Belt,24/7 Security,Temple,Community Hall', 'Water Supply,Electricity,Well-connected Roads', '{\"latitude\": 26.7356, \"longitude\": 83.4154}', 'APS Dream Homes Private Limited', '2025-09-30 18:09:29', '2025-09-30 18:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `commission_payouts`
--

CREATE TABLE `commission_payouts` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payout_date` date NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_transactions`
--

CREATE TABLE `commission_transactions` (
  `transaction_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `business_amount` decimal(12,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `level_difference_amount` decimal(10,2) DEFAULT 0.00,
  `upline_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commission_transactions`
--

INSERT INTO `commission_transactions` (`transaction_id`, `associate_id`, `booking_id`, `business_amount`, `commission_amount`, `commission_percentage`, `level_difference_amount`, `upline_id`, `transaction_date`, `status`) VALUES
(1, 1, 1, 15000000.00, 15000000.00, 99.99, 15000000.00, 1, '2025-04-30 18:30:00', ''),
(2, 2, 2, 7000000.00, 7000000.00, 99.99, 7000000.00, 2, '2025-05-06 18:30:00', 'pending'),
(3, 3, 3, 9000000.00, 9000000.00, 99.99, 9000000.00, 3, '2025-05-12 18:30:00', ''),
(4, 4, 4, 20000000.00, 20000000.00, 99.99, 20000000.00, 4, '2025-05-18 18:30:00', 'cancelled'),
(5, 5, 5, 25000000.00, 25000000.00, 99.99, 25000000.00, 5, '2025-05-24 18:30:00', ''),
(6, 1, 23, 6000000.00, 300000.00, 5.00, 0.00, NULL, '2025-09-24 17:55:35', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `type` enum('call','email','meeting','whatsapp','sms') DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `communication_date` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communications`
--

INSERT INTO `communications` (`id`, `lead_id`, `type`, `subject`, `notes`, `communication_date`, `user_id`, `created_at`) VALUES
(1, 1, '', 'Value for subject 1', 'This is a sample message for record 1.', '2025-05-01 00:00:00', 1, '2025-04-30 18:30:00'),
(2, 2, '', 'Value for subject 2', 'This is a sample message for record 2.', '2025-05-07 00:00:00', 2, '2025-05-06 18:30:00'),
(3, 3, '', 'Value for subject 3', 'This is a sample message for record 3.', '2025-05-13 00:00:00', 3, '2025-05-12 18:30:00'),
(4, 4, '', 'Value for subject 4', 'This is a sample message for record 4.', '2025-05-19 00:00:00', 4, '2025-05-18 18:30:00'),
(5, 5, '', 'Value for subject 5', 'This is a sample message for record 5.', '2025-05-25 00:00:00', 5, '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `gstin`, `pan`, `created_at`) VALUES
(1, 'Dream Home Pvt Ltd', '123 Main Road, City', '22AAAAA0000A1Z5', 'AAAAA0000A', '2025-04-21 20:38:10'),
(2, 'Rahul Sharma', 'Delhi', 'Value for gstin 1', 'Value for pan 1', '2025-04-30 18:30:00'),
(3, 'Priya Singh', 'Mumbai', 'Value for gstin 2', 'Value for pan 2', '2025-05-06 18:30:00'),
(4, 'Amit Kumar', 'Bangalore', 'Value for gstin 3', 'Value for pan 3', '2025-05-12 18:30:00'),
(5, 'Neha Patel', 'Ahmedabad', 'Value for gstin 4', 'Value for pan 4', '2025-05-18 18:30:00'),
(6, 'Vikram Mehta', 'Pune', 'Value for gstin 5', 'Value for pan 5', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `company_employees`
--

CREATE TABLE `company_employees` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_employees`
--

INSERT INTO `company_employees` (`id`, `company_id`, `user_id`, `position`, `salary`, `join_date`, `status`) VALUES
(1, 1, 10, 'Manager', 50000.00, '2024-01-15', 'active'),
(2, 1, 11, 'Accountant', 30000.00, '2024-02-01', 'active'),
(3, 1, 1, 'Value for position 1', 1000.00, '2025-05-01', 'active'),
(4, 2, 2, 'Value for position 2', 2000.00, '2025-05-07', ''),
(5, 3, 3, 'Value for position 3', 3000.00, '2025-05-13', ''),
(6, 4, 4, 'Value for position 4', 4000.00, '2025-05-19', ''),
(7, 5, 5, 'Value for position 5', 5000.00, '2025-05-25', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `company_projects`
--

CREATE TABLE `company_projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `project_type` enum('residential','commercial','mixed') DEFAULT 'residential',
  `status` enum('planning','ongoing','completed','cancelled') DEFAULT 'planning',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_property_levels`
--

CREATE TABLE `company_property_levels` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `level_name` varchar(100) NOT NULL,
  `level_order` int(11) NOT NULL,
  `direct_commission_percentage` decimal(5,2) NOT NULL,
  `team_commission_percentage` decimal(5,2) DEFAULT 0.00,
  `level_bonus_percentage` decimal(5,2) DEFAULT 0.00,
  `matching_bonus_percentage` decimal(5,2) DEFAULT 0.00,
  `leadership_bonus_percentage` decimal(5,2) DEFAULT 0.00,
  `monthly_target` decimal(15,2) NOT NULL,
  `min_plot_value` decimal(15,2) DEFAULT 0.00,
  `max_plot_value` decimal(15,2) DEFAULT 999999999.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_property_levels`
--

INSERT INTO `company_property_levels` (`id`, `plan_id`, `level_name`, `level_order`, `direct_commission_percentage`, `team_commission_percentage`, `level_bonus_percentage`, `matching_bonus_percentage`, `leadership_bonus_percentage`, `monthly_target`, `min_plot_value`, `max_plot_value`) VALUES
(1, 1, 'Associate', 1, 6.00, 2.00, 0.00, 0.00, 0.00, 1000000.00, 0.00, 10000000.00),
(2, 1, 'Sr. Associate', 2, 8.00, 3.00, 1.00, 2.00, 0.00, 3500000.00, 10000000.00, 50000000.00),
(3, 1, 'BDM', 3, 10.00, 4.00, 2.00, 3.00, 1.00, 7000000.00, 50000000.00, 150000000.00),
(4, 1, 'Sr. BDM', 4, 12.00, 5.00, 3.00, 4.00, 2.00, 15000000.00, 150000000.00, 500000000.00),
(5, 1, 'Vice President', 5, 15.00, 6.00, 4.00, 5.00, 3.00, 30000000.00, 500000000.00, 1000000000.00),
(6, 1, 'President', 6, 18.00, 7.00, 5.00, 6.00, 4.00, 50000000.00, 1000000000.00, 9999999999.00),
(7, 1, 'Site Manager', 7, 20.00, 8.00, 6.00, 7.00, 5.00, 100000000.00, 10000000000.00, 99999999999.00);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `phone`, `email`, `address`, `description`, `created_at`, `updated_at`) VALUES
(1, 'APS Dream Homes Pvt Ltd', '+91-7007444842', 'info@apsdreamhomes.com\r\napsdreamhomes44@gmail.com', '1st floor APS Dream Homes near ganpati lawn , Singhariya,\r\nkunraghat Gorakhpur, UP - 273008', 'Aps Dream Homes Private Limited, a active private limited company, was established on 26 April 2022 in Kunraghat, Uttar Pradesh, India. Engaging in real estate leasing within the real estate sector, it holds CIN: U70109UP2022PTC163047', '2025-09-26 17:49:30', '2025-10-04 16:20:08');

-- --------------------------------------------------------

--
-- Table structure for table `construction_projects`
--

CREATE TABLE `construction_projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `builder_id` int(11) NOT NULL,
  `site_id` int(11) DEFAULT NULL,
  `project_type` enum('residential','commercial','infrastructure','mixed_use') DEFAULT 'residential',
  `start_date` date DEFAULT NULL,
  `estimated_completion` date DEFAULT NULL,
  `actual_completion` date DEFAULT NULL,
  `budget_allocated` decimal(15,2) NOT NULL,
  `amount_spent` decimal(15,2) DEFAULT 0.00,
  `progress_percentage` int(11) DEFAULT 0,
  `status` enum('planning','in_progress','on_hold','completed','cancelled') DEFAULT 'planning',
  `description` text DEFAULT NULL,
  `contract_amount` decimal(15,2) DEFAULT NULL,
  `advance_paid` decimal(15,2) DEFAULT 0.00,
  `milestone_payments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`milestone_payments`)),
  `quality_rating` decimal(2,1) DEFAULT NULL,
  `completion_certificate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `construction_projects`
--
DELIMITER $$
CREATE TRIGGER `update_builder_counts_on_completion` AFTER UPDATE ON `construction_projects` FOR EACH ROW BEGIN
    IF OLD.status != 'completed' AND NEW.status = 'completed' THEN
        UPDATE builders
        SET completed_projects = completed_projects + 1,
            ongoing_projects = ongoing_projects - 1
        WHERE id = NEW.builder_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_builder_project_counts` AFTER INSERT ON `construction_projects` FOR EACH ROW BEGIN
    UPDATE builders
    SET total_projects = total_projects + 1,
        ongoing_projects = ongoing_projects + 1
    WHERE id = NEW.builder_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `crm_leads`
--

CREATE TABLE `crm_leads` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_type` enum('individual','corporate','nri') DEFAULT 'individual',
  `kyc_status` enum('not_submitted','pending','verified','rejected') DEFAULT 'not_submitted',
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `alternate_phone` varchar(15) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `monthly_income` decimal(15,2) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `referrer_code` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','blacklisted') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `customer_type`, `kyc_status`, `name`, `email`, `mobile`, `alternate_phone`, `date_of_birth`, `gender`, `address`, `city`, `state`, `pincode`, `aadhar_number`, `pan_number`, `occupation`, `company_name`, `monthly_income`, `referred_by`, `referrer_code`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 150, 'individual', 'pending', 'Demo Customer', 'demo.customer@aps.com', '9000040001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'active', NULL, '2025-04-23 01:25:34', '2025-09-24 22:29:20'),
(2, 166, 'individual', 'verified', 'Customer User', 'customer@demo.com', '7000000001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'active', NULL, '2025-04-23 01:26:09', '2025-09-24 22:29:20'),
(3, 184, 'individual', 'verified', 'Customer One', 'customer1@example.com', '9876532101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', NULL, '2024-01-10 04:30:00', '2025-09-24 22:29:20'),
(4, 185, 'individual', 'rejected', 'Customer Two', 'customer2@example.com', '9876532102', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'active', NULL, '2024-01-15 06:00:00', '2025-09-24 22:29:20'),
(5, 186, 'individual', 'pending', 'Customer Three', 'customer3@example.com', '9876532103', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'active', NULL, '2024-02-05 08:45:00', '2025-09-24 22:29:20'),
(6, 187, 'individual', 'verified', 'Customer Four', 'customer4@example.com', '9876532104', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2024-02-20 11:00:00', '2025-09-24 22:29:20'),
(7, 188, 'individual', 'rejected', 'Customer Five', 'customer5@example.com', '9876532105', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', NULL, '2024-03-01 04:15:00', '2025-09-24 22:29:20'),
(8, 209, 'individual', 'rejected', 'Customer One', 'customer6@example.com', '9874532101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'active', NULL, '2024-01-10 04:30:00', '2025-09-24 22:29:20'),
(9, 210, 'individual', 'pending', 'Customer Two', 'customer7@example.com', '987232102', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'active', NULL, '2024-01-15 06:00:00', '2025-09-24 22:29:20'),
(10, 211, 'individual', 'verified', 'Customer Three', 'customer8@example.com', '9676532103', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2024-02-05 08:45:00', '2025-09-24 22:29:20'),
(11, 212, 'individual', 'rejected', 'Customer Four', 'customer9@example.com', '9898532104', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', NULL, '2024-02-20 11:00:00', '2025-09-24 22:29:20'),
(12, 213, 'individual', 'pending', 'Customer Five', 'customer10@example.com', '98745932105', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'active', NULL, '2024-03-01 04:15:00', '2025-09-24 22:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `customers_ledger`
--

CREATE TABLE `customers_ledger` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `credit_days` int(11) DEFAULT 0,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `total_sales` decimal(15,2) DEFAULT 0.00,
  `total_payments` decimal(15,2) DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_documents`
--

CREATE TABLE `customer_documents` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `doc_name` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'uploaded',
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `blockchain_hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_documents`
--

INSERT INTO `customer_documents` (`id`, `customer_id`, `doc_name`, `status`, `uploaded_at`, `blockchain_hash`) VALUES
(1, 1, 'Rahul Sharma', 'active', '2025-05-01 00:00:00', 'Value for blockchain_hash 1'),
(2, 2, 'Priya Singh', 'pending', '2025-05-07 00:00:00', 'Value for blockchain_hash 2'),
(3, 3, 'Amit Kumar', 'completed', '2025-05-13 00:00:00', 'Value for blockchain_hash 3'),
(4, 4, 'Neha Patel', 'cancelled', '2025-05-19 00:00:00', 'Value for blockchain_hash 4'),
(5, 5, 'Vikram Mehta', 'active', '2025-05-25 00:00:00', 'Value for blockchain_hash 5');

-- --------------------------------------------------------

--
-- Table structure for table `customer_inquiries`
--

CREATE TABLE `customer_inquiries` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `inquiry_type` enum('general','payment','booking','technical','complaint') DEFAULT 'general',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `assigned_to` int(11) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_journeys`
--

CREATE TABLE `customer_journeys` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `journey` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`journey`)),
  `started_at` datetime DEFAULT current_timestamp(),
  `last_touch_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `customer_summary`
-- (See below for the actual view)
--
CREATE TABLE `customer_summary` (
`customer_id` int(11)
,`customer_name` varchar(100)
,`email` varchar(100)
,`mobile` varchar(20)
,`customer_type` enum('individual','corporate','nri')
,`kyc_status` enum('not_submitted','pending','verified','rejected')
,`total_bookings` bigint(21)
,`total_investment` decimal(37,2)
,`last_booking_date` date
,`days_since_last_booking` int(7)
,`customer_since` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `data_stream_events`
--

CREATE TABLE `data_stream_events` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `streamed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `drive_file_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `property_id`, `type`, `url`, `uploaded_on`, `drive_file_id`) VALUES
(1, 1, 1, 'agreement', 'docs/agreement1.pdf', '2025-04-21 20:54:15', NULL),
(2, 2, 2, 'kyc', 'docs/kyc_agent1.pdf', '2025-04-21 20:54:15', NULL),
(3, 1, 1, 'villa', 'Value for url 1', '0000-00-00 00:00:00', '1'),
(4, 2, 2, 'apartment', 'Value for url 2', '0000-00-00 00:00:00', '2'),
(5, 3, 3, 'house', 'Value for url 3', '0000-00-00 00:00:00', '3'),
(6, 4, 4, 'villa', 'Value for url 4', '0000-00-00 00:00:00', '4'),
(7, 5, 5, 'penthouse', 'Value for url 5', '0000-00-00 00:00:00', '5');

-- --------------------------------------------------------

--
-- Table structure for table `emi`
--

CREATE TABLE `emi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emi`
--

INSERT INTO `emi` (`id`, `user_id`, `property_id`, `amount`, `due_date`, `paid_date`, `status`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01', '2025-05-01', ''),
(2, 2, 2, 7000000.00, '2025-05-07', '2025-05-07', 'pending'),
(3, 3, 3, 9000000.00, '2025-05-13', '2025-05-13', ''),
(4, 4, 4, 20000000.00, '2025-05-19', '2025-05-19', ''),
(5, 5, 5, 25000000.00, '2025-05-25', '2025-05-25', '');

-- --------------------------------------------------------

--
-- Table structure for table `emi_installments`
--

CREATE TABLE `emi_installments` (
  `id` int(11) NOT NULL,
  `emi_plan_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `principal_amount` decimal(12,2) NOT NULL,
  `interest_amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_status` enum('pending','paid','overdue') NOT NULL DEFAULT 'pending',
  `payment_id` int(11) DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `last_reminder_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emi_plans`
--

CREATE TABLE `emi_plans` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `tenure_months` int(11) NOT NULL,
  `emi_amount` decimal(12,2) NOT NULL,
  `down_payment` decimal(12,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','completed','defaulted','cancelled') NOT NULL DEFAULT 'active',
  `foreclosure_date` date DEFAULT NULL,
  `foreclosure_amount` decimal(12,2) DEFAULT NULL,
  `foreclosure_payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emi_schedule`
--

CREATE TABLE `emi_schedule` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `emi_number` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue','waived') DEFAULT 'pending',
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `payment_id` int(11) DEFAULT NULL,
  `reminder_sent` int(11) DEFAULT 0,
  `last_reminder` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emi_schedule`
--

INSERT INTO `emi_schedule` (`id`, `customer_id`, `booking_id`, `emi_number`, `amount`, `due_date`, `status`, `paid_date`, `paid_amount`, `late_fee`, `payment_id`, `reminder_sent`, `last_reminder`, `created_at`, `updated_at`) VALUES
(1, 9, 5, 1, 63750.00, '2024-04-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(2, 9, 5, 2, 63750.00, '2024-05-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(3, 9, 5, 3, 63750.00, '2024-06-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(4, 9, 5, 4, 63750.00, '2024-07-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(5, 9, 5, 5, 63750.00, '2024-08-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(6, 9, 5, 6, 63750.00, '2024-09-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(7, 9, 5, 7, 63750.00, '2024-10-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(8, 9, 5, 8, 63750.00, '2024-11-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(9, 9, 5, 9, 63750.00, '2024-12-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(10, 9, 5, 10, 63750.00, '2025-01-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(11, 9, 5, 11, 63750.00, '2025-02-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(12, 9, 5, 12, 63750.00, '2025-03-04', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(13, 3, 9, 1, 63750.00, '2024-02-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(14, 3, 9, 2, 63750.00, '2024-03-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(15, 3, 9, 3, 63750.00, '2024-04-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(16, 3, 9, 4, 63750.00, '2024-05-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(17, 3, 9, 5, 63750.00, '2024-06-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(18, 3, 9, 6, 63750.00, '2024-07-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(19, 3, 9, 7, 63750.00, '2024-08-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(20, 3, 9, 8, 63750.00, '2024-09-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(21, 3, 9, 9, 63750.00, '2024-10-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(22, 3, 9, 10, 63750.00, '2024-11-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(23, 3, 9, 11, 63750.00, '2024-12-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(24, 3, 9, 12, 63750.00, '2025-01-22', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(25, 6, 10, 1, 63750.00, '2024-03-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(26, 6, 10, 2, 63750.00, '2024-04-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(27, 6, 10, 3, 63750.00, '2024-05-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(28, 6, 10, 4, 63750.00, '2024-06-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(29, 6, 10, 5, 63750.00, '2024-07-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(30, 6, 10, 6, 63750.00, '2024-08-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(31, 6, 10, 7, 63750.00, '2024-09-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(32, 6, 10, 8, 63750.00, '2024-10-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(33, 6, 10, 9, 63750.00, '2024-11-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(34, 6, 10, 10, 63750.00, '2024-12-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(35, 6, 10, 11, 63750.00, '2025-01-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(36, 6, 10, 12, 63750.00, '2025-02-12', 'pending', NULL, NULL, 0.00, NULL, 0, NULL, '2025-09-24 22:29:20', '2025-09-24 22:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `phone`, `role`, `salary`, `join_date`, `status`, `password`, `created_at`) VALUES
(1, 'Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(2, 'Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(3, 'Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(4, 'Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(5, 'Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:11:14'),
(6, 'Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(7, 'Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(8, 'Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(9, 'Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(10, 'Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:26:09'),
(11, 'Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(12, 'Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(13, 'Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(14, 'Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13'),
(15, 'Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', NULL, NULL, 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '2025-04-23 01:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `amount`, `source`, `expense_date`, `description`, `created_at`) VALUES
(1, 1, 15000000.00, 'Value for source 1', '2025-05-01', 'Beautiful luxury villa with garden and pool', '2025-04-30 18:30:00'),
(2, 2, 7000000.00, 'Value for source 2', '2025-05-07', 'Modern apartment in city center with great amenities', '2025-05-06 18:30:00'),
(3, 3, 9000000.00, 'Value for source 3', '2025-05-13', 'Spacious family home in quiet neighborhood', '2025-05-12 18:30:00'),
(4, 4, 20000000.00, 'Value for source 4', '2025-05-19', 'Beachfront luxury home with amazing views', '2025-05-18 18:30:00'),
(5, 5, 25000000.00, 'Value for source 5', '2025-05-25', 'Luxury penthouse with terrace and city views', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `category`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'What documents are required for property registration?', 'The documents required typically include sale deed, identity proof, address proof, property tax receipts, and NOC from relevant authorities.', 'General', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(2, 'How long does the property registration process take?', 'The registration process usually takes 3-7 working days, depending on the completeness of documents and government office workload.', 'General', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(3, 'What is title verification and why is it important?', 'Title verification is the process of checking the legal ownership history of a property to ensure there are no disputes or encumbrances. It\'s crucial to avoid future legal issues.', 'General', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(4, 'Can I register property without a lawyer?', 'While it\'s possible, we highly recommend professional legal assistance to ensure all documents are properly prepared and the process is completed correctly.', 'General', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(5, 'What are the common property disputes?', 'Common disputes include boundary issues, ownership claims, inheritance disputes, and unauthorized construction.', 'General', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(6, 'How much does legal documentation cost?', 'Costs vary based on property value and services required, typically ranging from ₹5,000 to ₹15,000 for complete documentation services.', 'General', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11');

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `land_area` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `kyc_doc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `user_id`, `land_area`, `location`, `kyc_doc`) VALUES
(1, 4, 2000.00, 'Village Area', 'kyc123.pdf'),
(2, 12, 3000.00, 'Village B', 'kyc_farmer1.pdf'),
(3, 1, 1000.00, 'Value for location 1', 'Value for kyc_doc 1'),
(4, 2, 2000.00, 'Value for location 2', 'Value for kyc_doc 2'),
(5, 3, 3000.00, 'Value for location 3', 'Value for kyc_doc 3'),
(6, 4, 4000.00, 'Value for location 4', 'Value for kyc_doc 4'),
(7, 5, 5000.00, 'Value for location 5', 'Value for kyc_doc 5');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_land_holdings`
--

CREATE TABLE `farmer_land_holdings` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `khasra_number` varchar(50) DEFAULT NULL,
  `land_area` decimal(10,2) NOT NULL,
  `land_area_unit` varchar(20) DEFAULT 'sqft',
  `land_type` enum('agricultural','residential','commercial','mixed') DEFAULT 'agricultural',
  `soil_type` varchar(100) DEFAULT NULL,
  `irrigation_source` varchar(100) DEFAULT NULL,
  `water_source` varchar(100) DEFAULT NULL,
  `electricity_available` tinyint(1) DEFAULT 0,
  `road_access` tinyint(1) DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `tehsil` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `land_value` decimal(15,2) DEFAULT NULL,
  `current_status` enum('cultivated','fallow','sold','under_acquisition','disputed') DEFAULT 'cultivated',
  `ownership_document` varchar(255) DEFAULT NULL,
  `mutation_document` varchar(255) DEFAULT NULL,
  `acquisition_status` enum('not_acquired','under_negotiation','acquired','rejected') DEFAULT 'not_acquired',
  `acquisition_date` date DEFAULT NULL,
  `acquisition_amount` decimal(15,2) DEFAULT NULL,
  `payment_status` enum('pending','partial','completed') DEFAULT 'pending',
  `payment_received` decimal(15,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmer_profiles`
--

CREATE TABLE `farmer_profiles` (
  `id` int(11) NOT NULL,
  `farmer_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `spouse_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'male',
  `phone` varchar(15) NOT NULL,
  `alternate_phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `post_office` varchar(100) DEFAULT NULL,
  `tehsil` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `voter_id` varchar(20) DEFAULT NULL,
  `bank_account_number` varchar(30) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `account_holder_name` varchar(100) DEFAULT NULL,
  `total_land_holding` decimal(10,2) DEFAULT 0.00,
  `cultivated_area` decimal(10,2) DEFAULT 0.00,
  `irrigated_area` decimal(10,2) DEFAULT 0.00,
  `non_irrigated_area` decimal(10,2) DEFAULT 0.00,
  `crop_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`crop_types`)),
  `farming_experience` int(11) DEFAULT 0,
  `education_level` varchar(50) DEFAULT NULL,
  `family_members` int(11) DEFAULT 0,
  `family_income` decimal(15,2) DEFAULT NULL,
  `credit_score` enum('excellent','good','fair','poor') DEFAULT 'fair',
  `credit_limit` decimal(15,2) DEFAULT 50000.00,
  `outstanding_loans` decimal(15,2) DEFAULT 0.00,
  `payment_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_history`)),
  `status` enum('active','inactive','blacklisted','under_review') DEFAULT 'active',
  `associate_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `message`, `status`, `created_at`) VALUES
(1, NULL, NULL, 'new', '2025-05-17 12:21:22'),
(4, 1, 'This is a sample message for record 1.', 'active', '2025-04-30 18:30:00'),
(5, 2, 'This is a sample message for record 2.', 'pending', '2025-05-06 18:30:00'),
(6, 3, 'This is a sample message for record 3.', 'completed', '2025-05-12 18:30:00'),
(7, 4, 'This is a sample message for record 4.', 'cancelled', '2025-05-18 18:30:00'),
(8, 5, 'This is a sample message for record 5.', 'active', '2025-05-24 18:30:00'),
(9, NULL, NULL, '1', '2025-05-25 12:18:12'),
(10, NULL, NULL, '1', '2025-05-25 12:18:12');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_tickets`
--

CREATE TABLE `feedback_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_tickets`
--

INSERT INTO `feedback_tickets` (`id`, `user_id`, `message`, `status`, `created_at`) VALUES
(1, 1, 'This is a sample message for record 1.', '', '2025-05-01 00:00:00'),
(2, 2, 'This is a sample message for record 2.', '', '2025-05-07 00:00:00'),
(3, 3, 'This is a sample message for record 3.', '', '2025-05-13 00:00:00'),
(4, 4, 'This is a sample message for record 4.', '', '2025-05-19 00:00:00'),
(5, 5, 'This is a sample message for record 5.', '', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `financial_reports`
--

CREATE TABLE `financial_reports` (
  `id` int(11) NOT NULL,
  `report_type` enum('profit_loss','balance_sheet','cash_flow','trial_balance','ledger','aging','gst_summary') NOT NULL,
  `report_period` varchar(50) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `report_data` longtext NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_cached` tinyint(1) DEFAULT 1,
  `cache_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_years`
--

CREATE TABLE `financial_years` (
  `id` int(11) NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `is_closed` tinyint(1) DEFAULT 0,
  `closing_date` date DEFAULT NULL,
  `opening_balances_set` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_years`
--

INSERT INTO `financial_years` (`id`, `year_name`, `start_date`, `end_date`, `is_current`, `is_closed`, `closing_date`, `opening_balances_set`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2024-25', '2024-04-01', '2025-03-31', 1, 0, NULL, 0, 1, '2025-09-24 21:22:28', '2025-09-24 21:22:28');

-- --------------------------------------------------------

--
-- Table structure for table `foreclosure_logs`
--

CREATE TABLE `foreclosure_logs` (
  `id` int(11) NOT NULL,
  `emi_plan_id` int(11) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `message` text DEFAULT NULL,
  `attempted_by` int(11) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `image_path`, `caption`, `status`, `created_at`) VALUES
(1, '/assets/images/gallery/project1.jpg', 'Raghunath Nagri Project Site View', 'active', '2025-04-30 06:27:19'),
(2, '/assets/images/gallery/project2.jpg', 'Modern Apartment Block', 'active', '2025-04-30 06:27:19'),
(3, '/assets/images/gallery/project3.jpg', 'Clubhouse and Amenities', 'active', '2025-04-30 06:27:19'),
(4, 'Value for image_path 1', 'Value for caption 1', 'active', '2025-04-30 18:30:00'),
(5, 'Value for image_path 2', 'Value for caption 2', '', '2025-05-06 18:30:00'),
(6, 'Value for image_path 3', 'Value for caption 3', '', '2025-05-12 18:30:00'),
(7, 'Value for image_path 4', 'Value for caption 4', '', '2025-05-18 18:30:00'),
(8, 'Value for image_path 5', 'Value for caption 5', 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `global_payments`
--

CREATE TABLE `global_payments` (
  `id` int(11) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'INR',
  `purpose` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `global_payments`
--

INSERT INTO `global_payments` (`id`, `client`, `amount`, `currency`, `purpose`, `status`, `created_at`) VALUES
(1, 'Value for client 1', 15000000.00, 'Value for ', 'Value for purpose 1', 'active', '2025-05-01 00:00:00'),
(2, 'Value for client 2', 7000000.00, 'Value for ', 'Value for purpose 2', 'pending', '2025-05-07 00:00:00'),
(3, 'Value for client 3', 9000000.00, 'Value for ', 'Value for purpose 3', 'completed', '2025-05-13 00:00:00'),
(4, 'Value for client 4', 20000000.00, 'Value for ', 'Value for purpose 4', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Value for client 5', 25000000.00, 'Value for ', 'Value for purpose 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `gst_records`
--

CREATE TABLE `gst_records` (
  `id` int(11) NOT NULL,
  `transaction_type` enum('sale','purchase','expense','income') NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `transaction_date` date NOT NULL,
  `party_name` varchar(255) NOT NULL,
  `party_gstin` varchar(20) DEFAULT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `taxable_amount` decimal(15,2) NOT NULL,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `cgst_amount` decimal(15,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_amount` decimal(15,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_amount` decimal(15,2) DEFAULT 0.00,
  `cess_rate` decimal(5,2) DEFAULT 0.00,
  `cess_amount` decimal(15,2) DEFAULT 0.00,
  `total_tax` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `place_of_supply` varchar(100) DEFAULT NULL,
  `reverse_charge` tinyint(1) DEFAULT 0,
  `gst_return_period` varchar(10) DEFAULT NULL,
  `filed_in_gstr` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hybrid_commission_plans`
--

CREATE TABLE `hybrid_commission_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `plan_code` varchar(50) NOT NULL,
  `plan_type` enum('company_mlm','resell_fixed','hybrid') NOT NULL,
  `description` text DEFAULT NULL,
  `total_commission_percentage` decimal(5,2) NOT NULL,
  `company_commission_percentage` decimal(5,2) DEFAULT 0.00,
  `resell_commission_percentage` decimal(5,2) DEFAULT 0.00,
  `development_cost_included` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive','draft') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hybrid_commission_plans`
--

INSERT INTO `hybrid_commission_plans` (`id`, `plan_name`, `plan_code`, `plan_type`, `description`, `total_commission_percentage`, `company_commission_percentage`, `resell_commission_percentage`, `development_cost_included`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Hybrid Real Estate Plan V1', 'HYBRID_V1', 'hybrid', 'Complete hybrid plan for company plotting and resell properties with 20% total commission', 20.00, 15.00, 5.00, 1, 'active', 1, '2025-09-25 22:04:28', '2025-09-25 22:04:28'),
(2, 'Company Only MLM', 'COMPANY_MLM_V1', 'company_mlm', 'MLM structure only for company developed properties', 15.00, 15.00, 0.00, 1, 'active', 1, '2025-09-25 22:04:28', '2025-09-25 22:04:28'),
(3, 'Resell Commission Plan', 'RESELL_V1', 'resell_fixed', 'Fixed commission structure for resell properties', 5.00, 0.00, 5.00, 0, 'active', 1, '2025-09-25 22:04:28', '2025-09-25 22:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `hybrid_commission_records`
--

CREATE TABLE `hybrid_commission_records` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sale_amount` decimal(15,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `commission_type` enum('company_mlm','resell_fixed','direct') NOT NULL,
  `commission_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`commission_breakdown`)),
  `level_achieved` varchar(100) DEFAULT NULL,
  `payout_status` enum('pending','approved','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `income_records`
--

CREATE TABLE `income_records` (
  `id` int(11) NOT NULL,
  `income_number` varchar(50) NOT NULL,
  `income_date` date NOT NULL,
  `income_category` varchar(255) NOT NULL,
  `income_subcategory` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','online','upi','card') NOT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurring_frequency` enum('monthly','quarterly','yearly') DEFAULT NULL,
  `status` enum('pending','received','cancelled') DEFAULT 'received',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `plot_id` int(11) DEFAULT NULL,
  `action` enum('created','booked','sold','transferred','released') DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`id`, `plot_id`, `action`, `user_id`, `note`, `action_date`, `created_at`) VALUES
(1, 1, 'booked', 1, 'Value for note 1', '2025-05-01 00:00:00', '2025-04-30 18:30:00'),
(2, 2, 'sold', 2, 'Value for note 2', '2025-05-07 00:00:00', '2025-05-06 18:30:00'),
(3, 3, 'transferred', 3, 'Value for note 3', '2025-05-13 00:00:00', '2025-05-12 18:30:00'),
(4, 4, 'released', 4, 'Value for note 4', '2025-05-19 00:00:00', '2025-05-18 18:30:00'),
(5, 5, 'created', 5, 'Value for note 5', '2025-05-25 00:00:00', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `iot_devices`
--

CREATE TABLE `iot_devices` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_type` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `last_seen` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iot_devices`
--

INSERT INTO `iot_devices` (`id`, `property_id`, `device_name`, `device_type`, `status`, `last_seen`, `created_at`) VALUES
(1, 1, 'Rahul Sharma', 'villa', 'active', '2025-05-01 00:00:00', '2025-05-01 00:00:00'),
(2, 2, 'Priya Singh', 'apartment', 'pending', '2025-05-02 00:00:00', '2025-05-07 00:00:00'),
(3, 3, 'Amit Kumar', 'house', 'completed', '2025-05-03 00:00:00', '2025-05-13 00:00:00'),
(4, 4, 'Neha Patel', 'villa', 'cancelled', '2025-05-04 00:00:00', '2025-05-19 00:00:00'),
(5, 5, 'Vikram Mehta', 'penthouse', 'active', '2025-05-05 00:00:00', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `iot_device_events`
--

CREATE TABLE `iot_device_events` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_value` varchar(255) DEFAULT NULL,
  `event_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iot_device_events`
--

INSERT INTO `iot_device_events` (`id`, `device_id`, `event_type`, `event_value`, `event_time`) VALUES
(1, 1, 'villa', 'Value for event_value 1', '2025-05-01 00:00:00'),
(2, 2, 'apartment', 'Value for event_value 2', '2025-05-02 00:00:00'),
(3, 3, 'house', 'Value for event_value 3', '2025-05-03 00:00:00'),
(4, 4, 'villa', 'Value for event_value 4', '2025-05-04 00:00:00'),
(5, 5, 'penthouse', 'Value for event_value 5', '2025-05-05 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL,
  `journal_number` varchar(50) NOT NULL,
  `entry_date` date NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `total_debit` decimal(15,2) NOT NULL,
  `total_credit` decimal(15,2) NOT NULL,
  `entry_type` enum('manual','system','adjustment','closing') DEFAULT 'manual',
  `source_document` enum('invoice','payment','expense','transfer','adjustment','opening') DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `status` enum('draft','approved','rejected') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entry_details`
--

CREATE TABLE `journal_entry_details` (
  `id` int(11) NOT NULL,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `debit_amount` decimal(15,2) DEFAULT 0.00,
  `credit_amount` decimal(15,2) DEFAULT 0.00,
  `reference_type` enum('customer','supplier','bank','employee','other') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jwt_blacklist`
--

CREATE TABLE `jwt_blacklist` (
  `id` int(11) NOT NULL,
  `token` varchar(500) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `land_purchases`
--

CREATE TABLE `land_purchases` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `registry_no` varchar(100) DEFAULT NULL,
  `agreement_doc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `land_purchases`
--

INSERT INTO `land_purchases` (`id`, `farmer_id`, `property_id`, `purchase_date`, `amount`, `registry_no`, `agreement_doc`) VALUES
(1, 1, 1, '2025-04-22', 1500000.00, 'REG123', 'agreement123.pdf'),
(2, 2, 3, '2025-04-15', 1400000.00, 'REG124', 'agreement124.pdf'),
(3, 1, 1, '2025-05-01', 15000000.00, 'Value for registry_no 1', 'Value for agreement_doc 1'),
(4, 2, 2, '2025-05-07', 7000000.00, 'Value for registry_no 2', 'Value for agreement_doc 2'),
(5, 3, 3, '2025-05-13', 9000000.00, 'Value for registry_no 3', 'Value for agreement_doc 3'),
(6, 4, 4, '2025-05-19', 20000000.00, 'Value for registry_no 4', 'Value for agreement_doc 4'),
(7, 5, 5, '2025-05-25', 25000000.00, 'Value for registry_no 5', 'Value for agreement_doc 5');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `converted_at` datetime DEFAULT NULL,
  `converted_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `contact`, `source`, `assigned_to`, `status`, `notes`, `created_at`, `converted_at`, `converted_amount`) VALUES
(1, 'Lead 1', '+91 984199040', 'Referral', 139, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-05-17 01:36:38', 15000000.00),
(2, 'Lead 2', '+91 983365109', 'Referral', 142, 'contacted', NULL, '2025-05-17 20:06:37', NULL, NULL),
(3, 'Lead 3', '+91 987686354', 'Walk-in', 105, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-03-24 01:36:38', 9000000.00),
(4, 'Lead 4', '+91 981782466', 'Property Portal', 106, 'negotiation', NULL, '2025-05-17 20:06:37', NULL, NULL),
(5, 'Lead 5', '+91 987868812', 'Property Portal', 105, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-04-16 01:36:38', 7000000.00),
(6, 'Lead 6', '+91 989431808', 'Walk-in', 142, 'qualified', NULL, '2025-05-17 20:06:37', NULL, NULL),
(7, 'Lead 7', '+91 986592157', 'Walk-in', 142, 'qualified', NULL, '2025-05-17 20:06:37', NULL, NULL),
(8, 'Lead 8', '+91 986077012', 'Property Portal', 105, 'closed_won', NULL, '2025-05-17 20:06:37', '2025-03-25 01:36:38', NULL),
(9, 'Lead 9', '+91 988862888', 'Website', 142, 'negotiation', NULL, '2025-05-17 20:06:37', NULL, NULL),
(10, 'Lead 10', '+91 985332951', 'Direct Call', 139, 'negotiation', NULL, '2025-05-17 20:06:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lead_files`
--

CREATE TABLE `lead_files` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_notes`
--

CREATE TABLE `lead_notes` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `leave_type`, `from_date`, `to_date`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 'villa', '2025-05-01', '2025-05-01', '', 'Value for remarks 1', '2025-04-30 18:30:00'),
(2, 2, 'apartment', '2025-05-07', '2025-05-07', 'pending', 'Value for remarks 2', '2025-05-06 18:30:00'),
(3, 3, 'house', '2025-05-13', '2025-05-13', '', 'Value for remarks 3', '2025-05-12 18:30:00'),
(4, 4, 'villa', '2025-05-19', '2025-05-19', '', 'Value for remarks 4', '2025-05-18 18:30:00'),
(5, 5, 'penthouse', '2025-05-25', '2025-05-25', '', 'Value for remarks 5', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `legal_documents`
--

CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `review_status` varchar(50) DEFAULT 'pending',
  `ai_summary` text DEFAULT NULL,
  `ai_flags` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `legal_documents`
--

INSERT INTO `legal_documents` (`id`, `file_name`, `file_url`, `review_status`, `ai_summary`, `ai_flags`, `uploaded_at`) VALUES
(1, 'Rahul Sharma', 'Value for file_url 1', 'active', 'Value for ai_summary 1', 'Value for ai_flags 1', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for file_url 2', 'pending', 'Value for ai_summary 2', 'Value for ai_flags 2', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for file_url 3', 'completed', 'Value for ai_summary 3', 'Value for ai_flags 3', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for file_url 4', 'cancelled', 'Value for ai_summary 4', 'Value for ai_flags 4', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for file_url 5', 'active', 'Value for ai_summary 5', 'Value for ai_flags 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `legal_services`
--

CREATE TABLE `legal_services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `price_range` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `legal_services`
--

INSERT INTO `legal_services` (`id`, `title`, `description`, `icon`, `price_range`, `duration`, `features`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Property Documentation', 'Complete property documentation services including title verification, registration, and mutation.', 'document', '₹5,000 - ₹15,000', '3-7 days', 'Title Verification|Registration Assistance|Mutation Services|Document Preparation', 'active', 0, '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(2, 'Legal Consultation', 'Expert legal consultation for property-related matters and dispute resolution.', 'consultation', '₹2,000 - ₹5,000', '1-2 hours', '30-min Consultation|Legal Advice|Document Review|Follow-up Support', 'active', 0, '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(3, 'Agreement Drafting', 'Professional drafting of property agreements, contracts, and legal documents.', 'drafting', '₹3,000 - ₹10,000', '2-5 days', 'Agreement Drafting|Contract Review|Customization|Legal Validation', 'active', 0, '2025-10-07 18:29:11', '2025-10-07 18:29:11');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `loan_number` varchar(50) NOT NULL,
  `loan_type` enum('business','personal','property','vehicle','equipment') NOT NULL,
  `lender_name` varchar(255) NOT NULL,
  `loan_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `tenure_months` int(11) NOT NULL,
  `emi_amount` decimal(15,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `disbursement_date` date DEFAULT NULL,
  `outstanding_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `bank_account_id` int(11) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `security_details` text DEFAULT NULL,
  `status` enum('applied','approved','disbursed','active','closed','defaulted') DEFAULT 'applied',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_emi_schedule`
--

CREATE TABLE `loan_emi_schedule` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `emi_number` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `emi_amount` decimal(15,2) NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_amount` decimal(15,2) NOT NULL,
  `outstanding_balance` decimal(15,2) NOT NULL,
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `late_fee` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','paid','overdue','partial') DEFAULT 'pending',
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketing_campaigns`
--

CREATE TABLE `marketing_campaigns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('email','sms') NOT NULL,
  `message` text NOT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'scheduled',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_campaigns`
--

INSERT INTO `marketing_campaigns` (`id`, `name`, `type`, `message`, `scheduled_at`, `status`, `created_at`) VALUES
(1, 'Rahul Sharma', '', 'This is a sample message for record 1.', '2025-05-01 00:00:00', 'active', '2025-05-01 00:00:00'),
(2, 'Priya Singh', '', 'This is a sample message for record 2.', '2025-05-07 00:00:00', 'pending', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', '', 'This is a sample message for record 3.', '2025-05-13 00:00:00', 'completed', '2025-05-13 00:00:00'),
(4, 'Neha Patel', '', 'This is a sample message for record 4.', '2025-05-19 00:00:00', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', '', 'This is a sample message for record 5.', '2025-05-25 00:00:00', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `marketing_strategies`
--

CREATE TABLE `marketing_strategies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_strategies`
--

INSERT INTO `marketing_strategies` (`id`, `title`, `description`, `image_url`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Limited Time Offer', 'Get an extra 5% discount on all bookings made this week. Hurry up!', 'assets/marketing/offer1.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(2, 'Free Site Visit', 'Book a free site visit for any property and get exclusive insights from our experts.', 'assets/marketing/offer2.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(3, 'Referral Bonus', 'Refer a friend and earn ???????10,000 on their first booking.', 'assets/marketing/offer3.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(4, 'Festive Bonanza', 'Special festive deals on select properties. Limited period only!', 'assets/marketing/offer4.jpg', 1, '2025-04-29 19:48:42', '2025-04-29 19:48:42'),
(5, 'Luxury Villa', 'Beautiful luxury villa with garden and pool', 'Value for image_url 1', 1, '2025-04-30 18:30:00', '2025-04-30 18:30:00'),
(6, 'City Apartment', 'Modern apartment in city center with great amenities', 'Value for image_url 2', 2, '2025-05-06 18:30:00', '2025-05-06 18:30:00'),
(7, 'Suburban House', 'Spacious family home in quiet neighborhood', 'Value for image_url 3', 3, '2025-05-12 18:30:00', '2025-05-12 18:30:00'),
(8, 'Beach Property', 'Beachfront luxury home with amazing views', 'Value for image_url 4', 4, '2025-05-18 18:30:00', '2025-05-18 18:30:00'),
(9, 'Penthouse', 'Luxury penthouse with terrace and city views', 'Value for image_url 5', 5, '2025-05-24 18:30:00', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_apps`
--

CREATE TABLE `marketplace_apps` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `app_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketplace_apps`
--

INSERT INTO `marketplace_apps` (`id`, `app_name`, `provider`, `app_url`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for provider 1', 'Value for app_url 1', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for provider 2', 'Value for app_url 2', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for provider 3', 'Value for app_url 3', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for provider 4', 'Value for app_url 4', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for provider 5', 'Value for app_url 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `version` varchar(20) NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `migration_name`, `applied_at`) VALUES
(1, '1.0.1', 'adduserpreferencestable', '2025-05-25 10:48:05'),
(2, '1.0.2', 'addapiauthenticationtables', '2025-05-25 10:52:15');

-- --------------------------------------------------------

--
-- Table structure for table `mlm_agents`
--

CREATE TABLE `mlm_agents` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `referral_code` varchar(20) NOT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `current_level` varchar(50) DEFAULT 'Associate',
  `total_business` decimal(15,2) DEFAULT 0.00,
  `total_team_size` int(11) DEFAULT 0,
  `direct_referrals` int(11) DEFAULT 0,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `registration_date` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mlm_agents`
--

INSERT INTO `mlm_agents` (`id`, `full_name`, `mobile`, `email`, `aadhar_number`, `pan_number`, `address`, `state`, `district`, `pin_code`, `bank_account`, `ifsc_code`, `referral_code`, `sponsor_id`, `password`, `current_level`, `total_business`, `total_team_size`, `direct_referrals`, `status`, `registration_date`, `last_login`) VALUES
(1, 'Amit Sharma', '9123456789', 'amit.sharma@apsdreamhome.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'APSAM8060', NULL, '$2y$10$95wpdC7dKPrtSqW0BpzonOHZQkrGpvZCKa8qbn.5CrHcNPCnav6dS', 'Associate', 0.00, 0, 0, 'active', '2025-09-24 00:00:00', '2025-09-26 13:54:08'),
(2, 'Test Associate', '9998887777', 'test.associate@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'APSTE6682', NULL, '$2y$10$oGz/JMVstlGonn1Lm0rSWeWftzYySgimimKBGRxtXEq.5Cv4loGve', 'Associate', 0.00, 0, 0, 'pending', '2025-09-25 02:11:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commissions`
--

CREATE TABLE `mlm_commissions` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `payout_id` int(11) DEFAULT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_analytics`
--

CREATE TABLE `mlm_commission_analytics` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `period_date` date NOT NULL,
  `total_earned` decimal(12,2) NOT NULL,
  `total_paid` decimal(12,2) NOT NULL,
  `pending_amount` decimal(12,2) NOT NULL,
  `direct_commissions` decimal(10,2) DEFAULT NULL,
  `team_commissions` decimal(10,2) DEFAULT NULL,
  `bonus_commissions` decimal(10,2) DEFAULT NULL,
  `rank_advances` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_ledger`
--

CREATE TABLE `mlm_commission_ledger` (
  `id` int(11) NOT NULL,
  `commission_id` int(11) NOT NULL,
  `action` enum('created','updated','paid','cancelled') NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_records`
--

CREATE TABLE `mlm_commission_records` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `booking_amount` decimal(15,2) NOT NULL,
  `commission_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`commission_details`)),
  `total_commission` decimal(12,2) NOT NULL,
  `status` enum('calculated','approved','paid','cancelled') DEFAULT 'calculated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_commission_targets`
--

CREATE TABLE `mlm_commission_targets` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `target_period` enum('monthly','quarterly','yearly') NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `achieved_amount` decimal(15,2) DEFAULT 0.00,
  `target_type` enum('personal_sales','team_sales','recruitment') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reward_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('active','achieved','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mlm_commission_targets`
--

INSERT INTO `mlm_commission_targets` (`id`, `associate_id`, `target_period`, `target_amount`, `achieved_amount`, `target_type`, `start_date`, `end_date`, `reward_amount`, `status`, `created_at`) VALUES
(1, 1, 'monthly', 500000.00, 0.00, 'personal_sales', '2025-09-01', '2025-09-30', 5000.00, 'active', '2025-09-25 21:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `mlm_payouts`
--

CREATE TABLE `mlm_payouts` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `payout_month` varchar(7) NOT NULL COMMENT 'YYYY-MM format',
  `total_commission` decimal(15,2) NOT NULL,
  `tds_amount` decimal(15,2) DEFAULT 0.00,
  `admin_charges` decimal(15,2) DEFAULT 0.00,
  `net_payout` decimal(15,2) NOT NULL,
  `status` enum('calculated','processed','paid','failed') DEFAULT 'calculated',
  `payment_method` enum('bank_transfer','upi','cheque','cash') DEFAULT 'bank_transfer',
  `payment_reference` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `mlm_performance`
-- (See below for the actual view)
--
CREATE TABLE `mlm_performance` (
`associate_id` int(11)
,`associate_name` varchar(100)
,`commission_rate` decimal(5,2)
,`status` enum('active','inactive','suspended','terminated')
,`total_referrals` bigint(21)
,`total_sales` bigint(21)
,`total_sales_amount` decimal(37,2)
,`estimated_commission` decimal(46,8)
);

-- --------------------------------------------------------

--
-- Table structure for table `mlm_rank_advancements`
--

CREATE TABLE `mlm_rank_advancements` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `previous_level` varchar(50) NOT NULL,
  `new_level` varchar(50) NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL,
  `payout_status` enum('pending','paid') DEFAULT 'pending',
  `advancement_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mlm_tree`
--

CREATE TABLE `mlm_tree` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `join_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mlm_tree`
--

INSERT INTO `mlm_tree` (`id`, `user_id`, `parent_id`, `level`, `join_date`) VALUES
(1, 1, 1, 1, '2025-05-01'),
(2, 2, 2, 2, '2025-05-07'),
(3, 3, 3, 3, '2025-05-13'),
(4, 4, 4, 4, '2025-05-19'),
(5, 5, 5, 5, '2025-05-25');

-- --------------------------------------------------------

--
-- Table structure for table `mlm_withdrawal_requests`
--

CREATE TABLE `mlm_withdrawal_requests` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `available_balance` decimal(12,2) NOT NULL,
  `status` enum('pending','approved','rejected','processed') DEFAULT 'pending',
  `request_date` date NOT NULL,
  `processed_date` date DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mobile_devices`
--

CREATE TABLE `mobile_devices` (
  `id` int(11) NOT NULL,
  `device_user` varchar(255) NOT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mobile_devices`
--

INSERT INTO `mobile_devices` (`id`, `device_user`, `push_token`, `platform`, `created_at`) VALUES
(1, 'Value for device_user 1', 'Value for push_token 1', 'Value for platform 1', '2025-05-01 00:00:00'),
(2, 'Value for device_user 2', 'Value for push_token 2', 'Value for platform 2', '2025-05-07 00:00:00'),
(3, 'Value for device_user 3', 'Value for push_token 3', 'Value for platform 3', '2025-05-13 00:00:00'),
(4, 'Value for device_user 4', 'Value for push_token 4', 'Value for platform 4', '2025-05-19 00:00:00'),
(5, 'Value for device_user 5', 'Value for push_token 5', 'Value for platform 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `summary` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `date`, `summary`, `image`, `content`, `created_at`) VALUES
(1, 'Welcome to APS Dream Homes!', '2025-04-01', 'We are excited to announce the launch of our new platform.', 'news1.jpg', 'Full content of the news article goes here.', '2025-04-23 08:19:00'),
(2, 'Market Update: April 2025', '2025-04-10', 'Latest trends and updates in the real estate market.', 'news2.jpg', 'Detailed content for market update.', '2025-04-23 08:19:00'),
(3, 'Luxury Villa', '2025-05-01', 'Value for summary 1', 'Value for image 1', 'This is a sample message for record 1.', '2025-04-30 18:30:00'),
(4, 'City Apartment', '2025-05-07', 'Value for summary 2', 'Value for image 2', 'This is a sample message for record 2.', '2025-05-06 18:30:00'),
(5, 'Suburban House', '2025-05-13', 'Value for summary 3', 'Value for image 3', 'This is a sample message for record 3.', '2025-05-12 18:30:00'),
(6, 'Beach Property', '2025-05-19', 'Value for summary 4', 'Value for image 4', 'This is a sample message for record 4.', '2025-05-18 18:30:00'),
(7, 'Penthouse', '2025-05-25', 'Value for summary 5', 'Value for image 5', 'This is a sample message for record 5.', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `created_at`, `read_at`) VALUES
(1, 1, 'Welcome to APS Dream Home!', 'info', '2025-05-17 20:06:37', NULL),
(2, 2, 'Your booking is confirmed.', 'booking', '2025-05-17 20:06:37', NULL),
(3, 10, 'Salary credited for March.', 'salary', '2025-05-17 20:06:37', NULL),
(4, 1, 'This is a sample message for record 1.', 'villa', '2025-05-26 20:06:37', '2025-04-30 18:30:00'),
(5, 2, 'This is a sample message for record 2.', 'apartment', '2025-06-01 20:06:37', '2025-05-06 18:30:00'),
(6, 3, 'This is a sample message for record 3.', 'house', '2025-06-07 20:06:37', '2025-05-12 18:30:00'),
(7, 4, 'This is a sample message for record 4.', 'villa', '2025-06-13 20:06:37', '2025-05-18 18:30:00'),
(8, 5, 'This is a sample message for record 5.', 'penthouse', '2025-06-19 20:06:37', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_logs`
--

INSERT INTO `notification_logs` (`id`, `notification_id`, `status`, `error_message`, `created_at`) VALUES
(1, 1, 'active', 'This is a sample message for record 1.', '2025-04-30 18:30:00'),
(2, 2, 'pending', 'This is a sample message for record 2.', '2025-05-06 18:30:00'),
(3, 3, 'completed', 'This is a sample message for record 3.', '2025-05-12 18:30:00'),
(9, 4, 'cancelled', 'This is a sample message for record 4.', '2025-05-18 18:30:00'),
(10, 5, 'active', 'This is a sample message for record 5.', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `push_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `user_id`, `type`, `email_enabled`, `push_enabled`, `sms_enabled`, `created_at`, `updated_at`) VALUES
(1, 1, 'villa', 1, 1, 1, '2025-04-30 18:30:00', '2025-04-30 18:30:00'),
(2, 2, 'apartment', 2, 2, 2, '2025-05-06 18:30:00', '2025-05-06 18:30:00'),
(3, 3, 'house', 3, 3, 3, '2025-05-12 18:30:00', '2025-05-12 18:30:00'),
(4, 4, 'villa', 4, 4, 4, '2025-05-18 18:30:00', '2025-05-18 18:30:00'),
(5, 5, 'penthouse', 5, 5, 5, '2025-05-24 18:30:00', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title_template` text NOT NULL,
  `message_template` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `type`, `title_template`, `message_template`, `created_at`, `updated_at`) VALUES
(1, 'new_lead', 'New Lead: {property_title}', 'You have received a new inquiry from {name} for {property_title}. Click here to view details.', '2025-05-17 06:30:15', NULL),
(2, 'visit_scheduled', 'Visit Scheduled: {property_title}', '{name} has scheduled a visit for {property_title} on {visit_date} at {visit_time}.', '2025-05-17 06:30:15', NULL),
(3, 'visit_reminder', 'Visit Reminder: {property_title}', 'Reminder: You have a property visit scheduled with {name} for {property_title} tomorrow at {visit_time}.', '2025-05-17 06:30:15', NULL),
(4, 'lead_update', 'Lead Status Update: {property_title}', 'The status of your inquiry for {property_title} has been updated to {status}.', '2025-05-17 06:30:15', NULL),
(5, 'lead_status_change', 'Lead Status Updated: {property_title}', 'The status of your lead for {property_title} has been updated to {lead_status}.', '2025-05-17 07:07:12', NULL),
(6, 'property_status_change', 'Property Status Change: {property_title}', 'The status of {property_title} has been changed to {property_status}.', '2025-05-17 07:07:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `value` decimal(12,2) DEFAULT NULL,
  `expected_close` date DEFAULT NULL,
  `status` enum('open','won','lost') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `opportunities`
--

INSERT INTO `opportunities` (`id`, `lead_id`, `stage`, `value`, `expected_close`, `status`, `created_at`) VALUES
(1, 1, 'Value for stage 1', 1000.00, '2025-05-01', '', '2025-04-30 18:30:00'),
(2, 2, 'Value for stage 2', 2000.00, '2025-05-02', '', '2025-05-06 18:30:00'),
(3, 3, 'Value for stage 3', 3000.00, '2025-05-03', '', '2025-05-12 18:30:00'),
(4, 4, 'Value for stage 4', 4000.00, '2025-05-04', '', '2025-05-18 18:30:00'),
(5, 5, 'Value for stage 5', 5000.00, '2025-05-05', '', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `partner_certification`
--

CREATE TABLE `partner_certification` (
  `id` int(11) NOT NULL,
  `partner_name` varchar(255) DEFAULT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `cert_status` varchar(50) DEFAULT 'pending',
  `revenue_share` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_certification`
--

INSERT INTO `partner_certification` (`id`, `partner_name`, `app_name`, `cert_status`, `revenue_share`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Rahul Sharma', 'active', 1, '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Priya Singh', 'pending', 2, '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Amit Kumar', 'completed', 3, '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Neha Patel', 'cancelled', 4, '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Vikram Mehta', 'active', 5, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `partner_rewards`
--

CREATE TABLE `partner_rewards` (
  `id` int(11) NOT NULL,
  `partner_email` varchar(255) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_rewards`
--

INSERT INTO `partner_rewards` (`id`, `partner_email`, `points`, `description`, `created_at`) VALUES
(1, 'rahul@example.com', 1, 'Beautiful luxury villa with garden and pool', '2025-05-01 00:00:00'),
(2, 'priya@example.com', 2, 'Modern apartment in city center with great amenities', '2025-05-07 00:00:00'),
(3, 'amit@example.com', 3, 'Spacious family home in quiet neighborhood', '2025-05-13 00:00:00'),
(4, 'neha@example.com', 4, 'Beachfront luxury home with amazing views', '2025-05-19 00:00:00'),
(5, 'vikram@example.com', 5, 'Luxury penthouse with terrace and city views', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_date`, `method`, `status`, `created_at`) VALUES
(1, 1, 2250000.00, '2024-01-09', 'debit_card', 'completed', '2025-09-24 22:29:20'),
(2, 2, 900000.00, '2024-01-09', 'net_banking', 'completed', '2025-09-24 22:29:20'),
(3, 3, 2500000.00, '2024-02-06', 'upi', 'completed', '2025-09-24 22:29:20'),
(4, 4, 7500000.00, '2024-02-06', 'credit_card', 'completed', '2025-09-24 22:29:20'),
(5, 5, 425000.00, '2024-03-05', 'debit_card', 'completed', '2025-09-24 22:29:20'),
(6, 7, 2500000.00, '2024-01-16', 'upi', 'completed', '2025-09-24 22:29:20'),
(7, 8, 7500000.00, '2024-01-16', 'credit_card', 'completed', '2025-09-24 22:29:20'),
(8, 9, 425000.00, '2024-01-23', 'debit_card', 'completed', '2025-09-24 22:29:20'),
(9, 10, 425000.00, '2024-02-13', 'net_banking', 'completed', '2025-09-24 22:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateway_config`
--

CREATE TABLE `payment_gateway_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateway_config`
--

INSERT INTO `payment_gateway_config` (`id`, `provider`, `api_key`, `api_secret`, `created_at`) VALUES
(1, 'Value for provider 1', 'Value for api_key 1', 'Value for api_secret 1', '2025-05-01 00:00:00'),
(2, 'Value for provider 2', 'Value for api_key 2', 'Value for api_secret 2', '2025-05-07 00:00:00'),
(3, 'Value for provider 3', 'Value for api_key 3', 'Value for api_secret 3', '2025-05-13 00:00:00'),
(4, 'Value for provider 4', 'Value for api_key 4', 'Value for api_secret 4', '2025-05-19 00:00:00'),
(5, 'Value for provider 5', 'Value for api_key 5', 'Value for api_secret 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `user_id`, `payment_method`, `amount`, `status`, `created_at`) VALUES
(1, 1, 'Value for payment_method 1', 15000000.00, 'active', '2025-04-30 18:30:00'),
(2, 2, 'Value for payment_method 2', 7000000.00, 'pending', '2025-05-06 18:30:00'),
(3, 3, 'Value for payment_method 3', 9000000.00, 'completed', '2025-05-12 18:30:00'),
(4, 4, 'Value for payment_method 4', 20000000.00, 'cancelled', '2025-05-18 18:30:00'),
(5, 5, 'Value for payment_method 5', 25000000.00, 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `payment_orders`
--

CREATE TABLE `payment_orders` (
  `id` int(11) NOT NULL,
  `razorpay_order_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'INR',
  `receipt` varchar(255) DEFAULT NULL,
  `status` enum('created','paid','failed','cancelled') DEFAULT 'created',
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `refund_id` varchar(255) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `payment_summary`
-- (See below for the actual view)
--
CREATE TABLE `payment_summary` (
`payment_id` int(11)
,`booking_id` int(11)
,`booking_number` varchar(50)
,`customer_id` int(11)
,`customer_name` varchar(100)
,`payment_amount` decimal(12,2)
,`payment_date` date
,`payment_method` varchar(50)
,`payment_status` enum('pending','completed','failed')
,`booking_amount` decimal(15,2)
,`total_paid_amount` decimal(34,2)
,`pending_amount` decimal(35,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `action`, `description`) VALUES
(1, 'Value for action 1', 'Beautiful luxury villa with garden and pool'),
(2, 'Value for action 2', 'Modern apartment in city center with great amenities'),
(3, 'Value for action 3', 'Spacious family home in quiet neighborhood'),
(4, 'Value for action 4', 'Beachfront luxury home with amazing views'),
(5, 'Value for action 5', 'Luxury penthouse with terrace and city views');

-- --------------------------------------------------------

--
-- Table structure for table `plots`
--

CREATE TABLE `plots` (
  `id` int(11) NOT NULL,
  `colonies_id` int(11) NOT NULL,
  `plot_number` varchar(50) NOT NULL,
  `size` decimal(10,2) NOT NULL COMMENT 'in square feet',
  `price` decimal(15,2) NOT NULL,
  `status` enum('available','booked','sold','blocked') DEFAULT 'available',
  `facing` varchar(50) DEFAULT NULL,
  `corner_plot` tinyint(1) DEFAULT 0,
  `booking_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plots`
--

INSERT INTO `plots` (`id`, `colonies_id`, `plot_number`, `size`, `price`, `status`, `facing`, `corner_plot`, `booking_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 'SYD-101', 2000.00, 1500000.00, 'sold', 'East', 0, 150000.00, '2025-09-30 18:12:02', '2025-09-30 18:12:02'),
(2, 1, 'SYD-102', 2200.00, 1650000.00, 'sold', 'North', 1, 165000.00, '2025-09-30 18:12:02', '2025-09-30 18:12:02'),
(3, 2, 'RN-201', 2500.00, 1800000.00, 'sold', 'South', 1, 180000.00, '2025-09-30 18:12:02', '2025-09-30 18:12:02'),
(4, 2, 'RN-202', 2300.00, 1740000.00, 'sold', 'East', 0, 174000.00, '2025-09-30 18:12:02', '2025-09-30 18:12:02'),
(5, 3, 'BN-301', 1500.00, 1300000.00, 'sold', 'North', 0, 130000.00, '2025-09-30 18:12:02', '2025-09-30 18:12:02'),
(6, 4, 'SB-401', 1200.00, 1100000.00, 'sold', 'West', 0, 110000.00, '2025-09-30 18:12:02', '2025-09-30 18:12:02');

-- --------------------------------------------------------

--
-- Table structure for table `plot_development`
--

CREATE TABLE `plot_development` (
  `id` int(11) NOT NULL,
  `land_purchase_id` int(11) NOT NULL,
  `plot_number` varchar(50) NOT NULL,
  `plot_size` decimal(10,2) NOT NULL COMMENT 'in sqft',
  `plot_type` enum('residential','commercial','agricultural') DEFAULT 'residential',
  `development_cost` decimal(15,2) DEFAULT 0.00,
  `selling_price` decimal(15,2) DEFAULT NULL,
  `status` enum('planned','under_development','ready_to_sell','sold','booked') DEFAULT 'planned',
  `customer_id` int(11) DEFAULT NULL,
  `sold_date` date DEFAULT NULL,
  `sold_price` decimal(15,2) DEFAULT NULL,
  `profit_loss` decimal(15,2) DEFAULT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `plot_facing` enum('north','south','east','west','northeast','northwest','southeast','southwest') DEFAULT NULL,
  `road_width` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `plot_development`
--
DELIMITER $$
CREATE TRIGGER `update_plot_profit_loss` BEFORE UPDATE ON `plot_development` FOR EACH ROW BEGIN
    IF NEW.status = 'sold' AND NEW.sold_price IS NOT NULL THEN
        SET NEW.profit_loss = NEW.sold_price - NEW.development_cost;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `plot_rate_calculations`
--

CREATE TABLE `plot_rate_calculations` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `land_cost` decimal(15,2) NOT NULL,
  `development_cost` decimal(15,2) NOT NULL,
  `total_commission` decimal(15,2) NOT NULL,
  `profit_margin` decimal(5,2) NOT NULL,
  `final_rate_per_sqft` decimal(10,2) NOT NULL,
  `calculated_by` int(11) NOT NULL,
  `calculation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'residential',
  `description` text DEFAULT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `builder_id` int(11) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `brochure_path` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `brochure_drive_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `location`, `type`, `description`, `tagline`, `meta_description`, `status`, `builder_id`, `project_name`, `start_date`, `end_date`, `budget`, `created_at`, `updated_at`, `brochure_path`, `youtube_url`, `brochure_drive_id`) VALUES
(1, 'Green Valley', 'Sector 10, City', 'residential', 'Premium residential plots', NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, '2025-04-21 21:09:43', '2025-04-21 21:09:43', NULL, NULL, NULL),
(2, 'Suryoday Colony ', 'jungle kaudiya to kalesar four lane', 'residential', 'Affordable housing project', NULL, NULL, 'active', NULL, 'suryoday Colony ', '2022-04-24', NULL, 100000000.00, '2025-04-21 21:09:43', '2025-04-30 06:37:56', NULL, NULL, NULL),
(3, 'Raghunath Nagri', 'Gorakhpur', 'residential', 'Premium residential project in Gorakhpur by APS Dream Homes. Modern amenities and prime location.', NULL, NULL, 'active', NULL, 'Raghunath Nagri', '2023-01-01', NULL, 50000000.00, '2025-04-30 06:22:23', '2025-04-30 06:22:23', NULL, NULL, NULL),
(4, 'Rahul Sharma', 'Value for location 1', 'residential', 'Beautiful luxury villa with garden and pool', NULL, NULL, 'active', 1, 'Rahul Sharma', '2025-05-01', '2025-05-01', 1000.00, '2025-04-30 18:30:00', '2025-04-30 18:30:00', 'Value for brochure_path 1', 'Value for youtube_url 1', '1'),
(5, 'Priya Singh', 'Value for location 2', 'residential', 'Modern apartment in city center with great amenities', NULL, NULL, '', 2, 'Priya Singh', '2025-05-07', '2025-05-07', 2000.00, '2025-05-06 18:30:00', '2025-05-06 18:30:00', 'Value for brochure_path 2', 'Value for youtube_url 2', '2'),
(6, 'Amit Kumar', 'Value for location 3', 'residential', 'Spacious family home in quiet neighborhood', NULL, NULL, '', 3, 'Amit Kumar', '2025-05-13', '2025-05-13', 3000.00, '2025-05-12 18:30:00', '2025-05-12 18:30:00', 'Value for brochure_path 3', 'Value for youtube_url 3', '3'),
(7, 'Neha Patel', 'Value for location 4', 'residential', 'Beachfront luxury home with amazing views', NULL, NULL, '', 4, 'Neha Patel', '2025-05-19', '2025-05-19', 4000.00, '2025-05-18 18:30:00', '2025-05-18 18:30:00', 'Value for brochure_path 4', 'Value for youtube_url 4', '4'),
(8, 'Vikram Mehta', 'Value for location 5', 'residential', 'Luxury penthouse with terrace and city views', NULL, NULL, 'active', 5, 'Vikram Mehta', '2025-05-25', '2025-05-25', 5000.00, '2025-05-24 18:30:00', '2025-05-24 18:30:00', 'Value for brochure_path 5', 'Value for youtube_url 5', '5'),
(9, NULL, NULL, 'residential', 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', NULL, NULL, 'active', 83, 'Raghunath Nagrii', '2024-04-05', '2026-07-30', 0.00, '2025-05-25 12:18:13', '2025-05-25 12:18:13', NULL, NULL, NULL),
(10, NULL, NULL, 'residential', 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', NULL, NULL, 'active', 83, 'Raghunath Nagrii', '2024-04-05', '2026-07-30', 0.00, '2025-05-25 12:18:13', '2025-05-25 12:18:13', NULL, NULL, NULL),
(11, NULL, NULL, 'residential', 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', NULL, NULL, 'active', 83, 'Raghunath Nagrii', '2024-04-05', '2026-07-30', 0.00, '2025-05-25 12:18:13', '2025-05-25 12:18:13', NULL, NULL, NULL),
(12, 'Sample Project', NULL, 'residential', NULL, NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, '2025-05-27 11:56:44', '2025-05-27 11:56:44', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_amenities`
--

CREATE TABLE `project_amenities` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `icon_path` varchar(255) NOT NULL,
  `label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_amenities`
--

INSERT INTO `project_amenities` (`id`, `project_id`, `icon_path`, `label`) VALUES
(1, 1, 'Value for icon_path 1', 'Value for label 1'),
(2, 2, 'Value for icon_path 2', 'Value for label 2'),
(3, 3, 'Value for icon_path 3', 'Value for label 3'),
(9, 4, 'Value for icon_path 4', 'Value for label 4'),
(10, 5, 'Value for icon_path 5', 'Value for label 5');

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_category_relations`
--

CREATE TABLE `project_category_relations` (
  `project_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_gallery`
--

CREATE TABLE `project_gallery` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `drive_file_id` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_gallery`
--

INSERT INTO `project_gallery` (`id`, `project_id`, `image_path`, `caption`, `drive_file_id`) VALUES
(1, 1, 'Value for image_path 1', 'Value for caption 1', '1'),
(2, 2, 'Value for image_path 2', 'Value for caption 2', '2'),
(3, 3, 'Value for image_path 3', 'Value for caption 3', '3'),
(9, 4, 'Value for image_path 4', 'Value for caption 4', '4'),
(10, 5, 'Value for image_path 5', 'Value for caption 5', '5');

-- --------------------------------------------------------

--
-- Table structure for table `project_progress`
--

CREATE TABLE `project_progress` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `progress_percentage` int(11) NOT NULL,
  `milestone_achieved` varchar(255) NOT NULL,
  `work_description` text NOT NULL,
  `amount_spent` decimal(15,2) DEFAULT 0.00,
  `next_milestone` varchar(255) DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `property_type_id` int(11) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `area_sqft` decimal(10,2) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('available','sold','reserved','under_construction') DEFAULT 'available',
  `featured` tinyint(1) DEFAULT 0,
  `hot_offer` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `slug`, `description`, `property_type_id`, `price`, `area_sqft`, `bedrooms`, `bathrooms`, `address`, `city`, `state`, `country`, `postal_code`, `latitude`, `longitude`, `status`, `featured`, `hot_offer`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Luxury Apartment in Bandra', 'luxury-apartment-bandra', 'Beautiful 3BHK apartment with sea view', 1, 25000000.00, 1800.00, 3, 3, '12 Hill Road', 'Mumbai', 'Maharashtra', 'India', '400050', NULL, NULL, 'available', 1, 0, NULL, NULL, '2024-01-05 04:30:00', '2025-09-24 22:29:20'),
(2, 'Modern Villa in Powai', 'modern-villa-powai', 'Spacious 4BHK villa with modern amenities', 3, 45000000.00, 3500.00, 4, 4, '34 Hiranandani Gardens', 'Mumbai', 'Maharashtra', 'India', '400076', NULL, NULL, 'available', 1, 0, NULL, NULL, '2024-01-10 06:00:00', '2025-09-24 22:29:20'),
(3, 'Cozy Studio in Andheri', 'cozy-studio-andheri', 'Compact studio apartment in prime location', 6, 8500000.00, 500.00, 1, 1, '56 SV Road', 'Mumbai', 'Maharashtra', 'India', '400058', NULL, NULL, 'available', 0, 0, NULL, NULL, '2024-01-15 08:45:00', '2025-09-24 22:29:20'),
(4, 'Luxury Penthouse in Worli', 'luxury-penthouse-worli', 'Exclusive penthouse with panoramic city views', 5, 75000000.00, 4500.00, 5, 5, '78 Dr. Annie Besant Road', 'Mumbai', 'Maharashtra', 'India', '400018', NULL, NULL, 'available', 1, 0, NULL, NULL, '2024-01-20 11:00:00', '2025-09-24 22:29:20'),
(5, 'Resale Flat in Juhu', 'resale-flat-juhu', 'Well-maintained 2BHK resale flat', 1, 18000000.00, 1100.00, 2, 2, '90 Juhu Tara Road', 'Mumbai', 'Maharashtra', 'India', '400049', NULL, NULL, 'sold', 0, 0, NULL, NULL, '2024-01-25 04:15:00', '2025-09-24 22:29:20'),
(6, 'Luxury Apartment in Gorakhpur City Center', '', 'Spacious 3 BHK apartment with modern amenities, located in the heart of Gorakhpur. Features include modular kitchen, attached bathrooms, and 24/7 security.', 1, 4500000.00, 1200.00, 3, 2, 'Medical College Road, Gorakhpur', 'Gorakhpur', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 1, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(7, 'Premium Villa in Lucknow Gomti Nagar', '', 'Beautiful 4 BHK villa with private garden, swimming pool, and modern interiors. Perfect for luxury living in Lucknow\'s most prestigious area.', 2, 15000000.00, 2500.00, 4, 3, 'Gomti Nagar Extension, Lucknow', 'Lucknow', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 1, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(8, 'Modern 2 BHK Apartment in Varanasi', '', 'Contemporary 2 BHK apartment near BHU campus. Features smart home technology, covered parking, and excellent connectivity.', 1, 3200000.00, 950.00, 2, 2, 'Lanka, Varanasi', 'Varanasi', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 0, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(9, 'Commercial Space in Kanpur Mall', '', 'Prime commercial space in Kanpur\'s busiest shopping mall. Ideal for retail business with high footfall and excellent visibility.', 5, 8000000.00, 800.00, 0, 1, 'Mall Road, Kanpur', 'Kanpur', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 1, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(10, 'Independent House in Allahabad', '', 'Charming 3 BHK independent house with garden and parking space. Located in peaceful residential area with good connectivity.', 3, 5500000.00, 1500.00, 3, 2, 'Civil Lines, Allahabad', 'Allahabad', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 0, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(11, 'Studio Apartment in Noida', '', 'Modern studio apartment in Noida\'s tech hub. Perfect for young professionals with gym, swimming pool, and metro connectivity.', 1, 2800000.00, 650.00, 1, 1, 'Sector 62, Noida', 'Noida', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 0, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(12, 'Duplex Villa in Ghaziabad', '', 'Luxurious duplex villa with modern architecture, private terrace, and premium fittings. Located in upscale Ghaziabad neighborhood.', 2, 12000000.00, 2200.00, 4, 3, 'Indirapuram, Ghaziabad', 'Ghaziabad', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 1, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(13, 'Plot for Sale in Meerut', '', 'Prime residential plot in developing area of Meerut. Ready for construction with all amenities and good connectivity.', 4, 2500000.00, 2000.00, 0, 0, 'Shastri Nagar, Meerut', 'Meerut', 'Uttar Pradesh', NULL, NULL, NULL, NULL, 'available', 0, 0, 1, NULL, '2025-10-20 19:58:59', '2025-10-20 19:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `status` enum('available','sold','pending') DEFAULT 'available',
  `type` enum('residential','commercial','land') DEFAULT 'residential',
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `gallery_images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_amenities`
--

CREATE TABLE `property_amenities` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amenity_name` varchar(100) NOT NULL,
  `amenity_type` varchar(50) DEFAULT 'basic',
  `amenity_icon` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_amenities`
--

INSERT INTO `property_amenities` (`id`, `property_id`, `amenity_name`, `amenity_type`, `amenity_icon`, `is_available`, `created_at`) VALUES
(1, 1, 'Swimming Pool', 'luxury', '🏊', 1, '2025-09-30 15:54:54'),
(2, 1, 'Gymnasium', 'luxury', '💪', 1, '2025-09-30 15:54:54'),
(3, 1, '24/7 Security', 'security', '🔒', 1, '2025-09-30 15:54:54'),
(4, 1, 'Parking', 'basic', '🅿️', 1, '2025-09-30 15:54:54');

-- --------------------------------------------------------

--
-- Table structure for table `property_bookings`
--

CREATE TABLE `property_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_order_id` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','refunded') DEFAULT 'pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmation_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_development_costs`
--

CREATE TABLE `property_development_costs` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `cost_type` enum('land_cost','construction','infrastructure','legal','marketing','commission','other') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `percentage_of_total` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_favorites`
--

CREATE TABLE `property_favorites` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_features`
--

CREATE TABLE `property_features` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_features`
--

INSERT INTO `property_features` (`id`, `name`, `icon`, `status`, `created_at`) VALUES
(1, 'Swimming Pool', 'fa-swimming-pool', 'active', '2025-09-24 22:29:20'),
(2, 'Gym', 'fa-dumbbell', 'active', '2025-09-24 22:29:20'),
(3, 'Parking', 'fa-parking', 'active', '2025-09-24 22:29:20'),
(4, 'Garden', 'fa-tree', 'active', '2025-09-24 22:29:20'),
(5, 'Security', 'fa-shield-alt', 'active', '2025-09-24 22:29:20'),
(6, 'Lift', 'fa-elevator', 'active', '2025-09-24 22:29:20'),
(7, 'Power Backup', 'fa-bolt', 'active', '2025-09-24 22:29:20'),
(8, 'Water Supply', 'fa-tint', 'active', '2025-09-24 22:29:20'),
(9, 'Club House', 'fa-home', 'active', '2025-09-24 22:29:20'),
(10, 'Play Area', 'fa-child', 'active', '2025-09-24 22:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `property_feature_mappings`
--

CREATE TABLE `property_feature_mappings` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_feature_mappings`
--

INSERT INTO `property_feature_mappings` (`id`, `property_id`, `feature_id`, `value`, `created_at`) VALUES
(1, 1, 1, 'Yes', '2025-09-24 22:29:20'),
(2, 1, 2, 'Yes', '2025-09-24 22:29:20'),
(3, 1, 3, '2 Covered', '2025-09-24 22:29:20'),
(4, 1, 4, 'Yes', '2025-09-24 22:29:20'),
(5, 1, 5, '24/7 Security', '2025-09-24 22:29:20'),
(6, 1, 6, '2 Lifts', '2025-09-24 22:29:20'),
(7, 1, 7, 'Full Backup', '2025-09-24 22:29:20'),
(8, 1, 8, '24/7 Supply', '2025-09-24 22:29:20'),
(9, 1, 9, 'Yes', '2025-09-24 22:29:20'),
(10, 1, 10, 'Children\'s Play Area', '2025-09-24 22:29:20'),
(11, 2, 1, 'Yes', '2025-09-24 22:29:20'),
(12, 2, 2, 'Yes', '2025-09-24 22:29:20'),
(13, 2, 3, '2 Covered', '2025-09-24 22:29:20'),
(14, 2, 4, 'Yes', '2025-09-24 22:29:20'),
(15, 2, 5, '24/7 Security', '2025-09-24 22:29:20'),
(16, 2, 6, '2 Lifts', '2025-09-24 22:29:20'),
(17, 2, 7, 'Full Backup', '2025-09-24 22:29:20'),
(18, 2, 8, '24/7 Supply', '2025-09-24 22:29:20'),
(19, 2, 9, 'Yes', '2025-09-24 22:29:20'),
(20, 2, 10, 'Children\'s Play Area', '2025-09-24 22:29:20'),
(21, 3, 1, 'Yes', '2025-09-24 22:29:20'),
(22, 3, 2, 'Yes', '2025-09-24 22:29:20'),
(23, 3, 3, '2 Covered', '2025-09-24 22:29:20'),
(24, 3, 4, 'Yes', '2025-09-24 22:29:20'),
(25, 3, 5, '24/7 Security', '2025-09-24 22:29:20'),
(26, 3, 6, '2 Lifts', '2025-09-24 22:29:20'),
(27, 3, 7, 'Full Backup', '2025-09-24 22:29:20'),
(28, 3, 8, '24/7 Supply', '2025-09-24 22:29:20'),
(29, 3, 9, 'Yes', '2025-09-24 22:29:20'),
(30, 3, 10, 'Children\'s Play Area', '2025-09-24 22:29:20'),
(31, 4, 1, 'Yes', '2025-09-24 22:29:20'),
(32, 4, 2, 'Yes', '2025-09-24 22:29:20'),
(33, 4, 3, '2 Covered', '2025-09-24 22:29:20'),
(34, 4, 4, 'Yes', '2025-09-24 22:29:20'),
(35, 4, 5, '24/7 Security', '2025-09-24 22:29:20'),
(36, 4, 6, '2 Lifts', '2025-09-24 22:29:20'),
(37, 4, 7, 'Full Backup', '2025-09-24 22:29:20'),
(38, 4, 8, '24/7 Supply', '2025-09-24 22:29:20'),
(39, 4, 9, 'Yes', '2025-09-24 22:29:20'),
(40, 4, 10, 'Children\'s Play Area', '2025-09-24 22:29:20'),
(41, 5, 1, 'Yes', '2025-09-24 22:29:20'),
(42, 5, 2, 'Yes', '2025-09-24 22:29:20'),
(43, 5, 3, '2 Covered', '2025-09-24 22:29:20'),
(44, 5, 4, 'Yes', '2025-09-24 22:29:20'),
(45, 5, 5, '24/7 Security', '2025-09-24 22:29:20'),
(46, 5, 6, '2 Lifts', '2025-09-24 22:29:20'),
(47, 5, 7, 'Full Backup', '2025-09-24 22:29:20'),
(48, 5, 8, '24/7 Supply', '2025-09-24 22:29:20'),
(49, 5, 9, 'Yes', '2025-09-24 22:29:20'),
(50, 5, 10, 'Children\'s Play Area', '2025-09-24 22:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop', 1, 1, '2025-09-26 20:20:30'),
(2, 2, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 2, '2025-09-26 20:20:30'),
(3, 4, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 1, 3, '2025-09-26 20:20:30'),
(4, 6, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(5, 6, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(6, 6, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(7, 7, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(8, 7, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(9, 7, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(10, 8, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(11, 8, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(12, 8, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(13, 9, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(14, 9, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(15, 9, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(16, 10, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(17, 10, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(18, 10, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(19, 11, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(20, 11, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(21, 11, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(22, 12, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(23, 12, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(24, 12, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59'),
(25, 13, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop', 1, 0, '2025-10-20 19:58:59'),
(26, 13, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop', 0, 1, '2025-10-20 19:58:59'),
(27, 13, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2, '2025-10-20 19:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Apartment', NULL, 'fa-building', 'active', '2025-05-27 12:00:11', '2025-05-27 12:00:11'),
(2, 'Apartment', 'Residential apartment units in a building', 'fa-building', 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(3, 'Villa', 'Independent house with private garden', 'fa-home', 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(4, 'Plot', 'Empty land for construction', 'fa-vector-square', 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(5, 'Penthouse', 'Luxury top-floor apartment', 'fa-building', 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(6, 'Studio', 'Single room apartment with kitchenette', 'fa-home', 'active', '2025-09-24 22:29:20', '2025-09-24 22:29:20'),
(7, 'Apartment', 'Residential apartments and flats', NULL, 'active', '2025-09-26 14:04:33', '2025-09-26 14:04:33'),
(8, 'Villa', 'Independent villas and bungalows', NULL, 'active', '2025-09-26 14:04:33', '2025-09-26 14:04:33'),
(9, 'House', 'Individual houses and duplexes', NULL, 'active', '2025-09-26 14:04:33', '2025-09-26 14:04:33'),
(10, 'Plot', 'Land and plots for construction', NULL, 'active', '2025-09-26 14:04:33', '2025-09-26 14:04:33'),
(11, 'Commercial', 'Commercial properties and offices', NULL, 'active', '2025-09-26 14:04:33', '2025-09-26 14:04:33'),
(12, 'Penthouse', 'Luxury penthouse apartments', NULL, 'active', '2025-09-26 14:04:33', '2025-09-26 14:04:33'),
(13, 'Apartment', 'Residential apartments and flats', NULL, 'active', '2025-09-26 14:08:11', '2025-09-26 14:08:11'),
(14, 'Villa', 'Independent villas and bungalows', NULL, 'active', '2025-09-26 14:08:11', '2025-09-26 14:08:11'),
(15, 'House', 'Individual houses and duplexes', NULL, 'active', '2025-09-26 14:08:11', '2025-09-26 14:08:11'),
(16, 'Plot', 'Land and plots for construction', NULL, 'active', '2025-09-26 14:08:11', '2025-09-26 14:08:11'),
(17, 'Commercial', 'Commercial properties and offices', NULL, 'active', '2025-09-26 14:08:11', '2025-09-26 14:08:11'),
(18, 'Penthouse', 'Luxury penthouse apartments', NULL, 'active', '2025-09-26 14:08:11', '2025-09-26 14:08:11'),
(19, 'Apartment', 'Residential apartments and flats', NULL, 'active', '2025-09-26 14:33:27', '2025-09-26 14:33:27'),
(20, 'Villa', 'Independent villas and bungalows', NULL, 'active', '2025-09-26 14:33:27', '2025-09-26 14:33:27'),
(21, 'House', 'Individual houses and duplexes', NULL, 'active', '2025-09-26 14:33:27', '2025-09-26 14:33:27'),
(22, 'Plot', 'Land and plots for construction', NULL, 'active', '2025-09-26 14:33:27', '2025-09-26 14:33:27'),
(23, 'Commercial', 'Commercial properties and offices', NULL, 'active', '2025-09-26 14:33:27', '2025-09-26 14:33:27'),
(24, 'Penthouse', 'Luxury penthouse apartments', NULL, 'active', '2025-09-26 14:33:27', '2025-09-26 14:33:27'),
(25, 'Residential Plot', 'Premium residential plots for building your dream home', NULL, 'active', '2025-09-26 17:49:30', '2025-09-26 17:49:30'),
(26, 'Apartment', 'Modern apartments with world-class amenities', NULL, 'active', '2025-09-26 17:49:30', '2025-09-26 17:49:30'),
(27, 'Villa', 'Luxurious villas with private gardens', NULL, 'active', '2025-09-26 17:49:30', '2025-09-26 17:49:30'),
(28, 'Commercial Shop', 'Commercial spaces for business', NULL, 'active', '2025-09-26 17:49:30', '2025-09-26 17:49:30'),
(29, 'Office Space', 'Professional office spaces', NULL, 'active', '2025-09-26 17:49:30', '2025-09-26 17:49:30'),
(30, 'Residential Plot', 'Premium residential plots for building your dream home', NULL, 'active', '2025-09-26 17:57:41', '2025-09-26 17:57:41'),
(31, 'Apartment', 'Modern apartments with world-class amenities', NULL, 'active', '2025-09-26 17:57:41', '2025-09-26 17:57:41'),
(32, 'Villa', 'Luxurious villas with private gardens', NULL, 'active', '2025-09-26 17:57:41', '2025-09-26 17:57:41'),
(33, 'Commercial Shop', 'Commercial spaces for business', NULL, 'active', '2025-09-26 17:57:41', '2025-09-26 17:57:41'),
(34, 'Office Space', 'Professional office spaces', NULL, 'active', '2025-09-26 17:57:41', '2025-09-26 17:57:41'),
(35, 'Apartment', 'Modern apartment units', NULL, 'active', '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(36, 'Villa', 'Independent luxury villas', NULL, 'active', '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(37, 'Independent House', 'Standalone residential houses', NULL, 'active', '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(38, 'Plot/Land', 'Residential and commercial plots', NULL, 'active', '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(39, 'Commercial', 'Commercial spaces and offices', NULL, 'active', '2025-10-20 19:58:59', '2025-10-20 19:58:59'),
(40, 'Studio', 'Studio apartments and lofts', NULL, 'active', '2025-10-20 19:58:59', '2025-10-20 19:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `property_visits`
--

CREATE TABLE `property_visits` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `visit_date` datetime NOT NULL,
  `visit_type` enum('site_visit','virtual_tour','office_meeting','follow_up') DEFAULT 'site_visit',
  `status` enum('scheduled','confirmed','completed','cancelled','rescheduled','no_show') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `feedback_rating` int(1) DEFAULT NULL,
  `feedback_comments` text DEFAULT NULL,
  `interest_level` enum('low','medium','high','very_high') DEFAULT 'medium',
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_date` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_visits`
--

INSERT INTO `property_visits` (`id`, `customer_id`, `property_id`, `associate_id`, `visit_date`, `visit_type`, `status`, `notes`, `feedback_rating`, `feedback_comments`, `interest_level`, `follow_up_required`, `follow_up_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-09-26 10:00:00', 'site_visit', 'scheduled', 'Initial property viewing. Customer interested in investment opportunity.', NULL, NULL, 'high', 1, '2025-09-27 14:00:00', 1, '2025-09-24 18:09:39', '2025-09-24 18:09:39'),
(2, 2, 2, 1, '2025-09-25 15:00:00', 'virtual_tour', 'confirmed', 'Virtual tour scheduled. Customer unable to visit physically due to location.', NULL, NULL, 'medium', 1, '2025-09-26 11:00:00', 1, '2025-09-24 18:09:39', '2025-09-24 18:09:39'),
(3, 11, 1, 1, '2025-09-23 16:00:00', 'site_visit', 'completed', 'Customer visited property and showed strong interest.', 5, 'Excellent property with great amenities. Considering purchase.', 'very_high', 1, '2025-09-25 10:00:00', 1, '2025-09-24 18:09:39', '2025-09-24 18:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `supplier_invoice_number` varchar(50) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','received','paid','partial','overdue') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoice_items`
--

CREATE TABLE `purchase_invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `real_estate_properties`
--

CREATE TABLE `real_estate_properties` (
  `id` int(11) NOT NULL,
  `property_code` varchar(50) NOT NULL,
  `property_name` varchar(255) NOT NULL,
  `property_type` enum('company','resell') NOT NULL,
  `property_category` enum('plot','flat','house','commercial','land') NOT NULL,
  `location` varchar(255) NOT NULL,
  `area_sqft` decimal(10,2) NOT NULL,
  `rate_per_sqft` decimal(10,2) NOT NULL,
  `total_value` decimal(15,2) NOT NULL,
  `development_cost` decimal(15,2) DEFAULT 0.00,
  `commission_percentage` decimal(5,2) NOT NULL,
  `status` enum('available','booked','sold','cancelled') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `real_estate_properties`
--

INSERT INTO `real_estate_properties` (`id`, `property_code`, `property_name`, `property_type`, `property_category`, `location`, `area_sqft`, `rate_per_sqft`, `total_value`, `development_cost`, `commission_percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PLOT-001', 'Green Valley Plot A1', 'company', 'plot', 'Sector 15, Gurgaon', 1000.00, 5000.00, 5000000.00, 2000000.00, 15.00, 'available', '2025-09-25 22:04:28', '2025-09-25 22:04:28'),
(2, 'PLOT-002', 'Sunrise Colony Plot B5', 'company', 'plot', 'Sector 22, Noida', 1500.00, 4500.00, 6750000.00, 2500000.00, 15.00, 'available', '2025-09-25 22:04:28', '2025-09-25 22:04:28'),
(3, 'RESELL-001', 'DLF Phase 2 Flat', 'resell', 'flat', 'DLF Phase 2, Gurgaon', 1200.00, 15000.00, 18000000.00, 0.00, 3.00, 'available', '2025-09-25 22:04:28', '2025-09-25 22:04:28'),
(4, 'RESELL-002', 'Independent House', 'resell', 'house', 'Sector 45, Noida', 2000.00, 8000.00, 16000000.00, 0.00, 3.00, 'available', '2025-09-25 22:04:28', '2025-09-25 22:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `recurring_transactions`
--

CREATE TABLE `recurring_transactions` (
  `id` int(11) NOT NULL,
  `transaction_name` varchar(255) NOT NULL,
  `transaction_type` enum('income','expense','transfer') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `frequency` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `next_due_date` date NOT NULL,
  `account_id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `auto_create` tinyint(1) DEFAULT 0,
  `status` enum('active','paused','completed','cancelled') DEFAULT 'active',
  `created_transactions` int(11) DEFAULT 0,
  `last_created_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_properties`
--

CREATE TABLE `rental_properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `rent_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('available','rented','inactive') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_properties`
--

INSERT INTO `rental_properties` (`id`, `owner_id`, `address`, `rent_amount`, `status`) VALUES
(1, 1, 'Delhi', 15000000.00, ''),
(2, 2, 'Mumbai', 7000000.00, ''),
(3, 3, 'Bangalore', 9000000.00, ''),
(4, 4, 'Ahmedabad', 20000000.00, ''),
(5, 5, 'Pune', 25000000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `rent_payments`
--

CREATE TABLE `rent_payments` (
  `id` int(11) NOT NULL,
  `rental_property_id` int(11) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rent_payments`
--

INSERT INTO `rent_payments` (`id`, `rental_property_id`, `tenant_id`, `amount`, `due_date`, `paid_date`, `status`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01', '2025-05-01', ''),
(2, 2, 2, 7000000.00, '2025-05-07', '2025-05-07', 'pending'),
(3, 3, 3, 9000000.00, '2025-05-13', '2025-05-13', ''),
(4, 4, 4, 20000000.00, '2025-05-19', '2025-05-19', ''),
(5, 5, 5, 25000000.00, '2025-05-25', '2025-05-25', '');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_for_month` int(11) NOT NULL,
  `generated_for_year` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resale_commissions`
--

CREATE TABLE `resale_commissions` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `resale_property_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `paid_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resale_commissions`
--

INSERT INTO `resale_commissions` (`id`, `associate_id`, `resale_property_id`, `amount`, `paid_on`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01'),
(2, 2, 2, 7000000.00, '2025-05-02'),
(3, 3, 3, 9000000.00, '2025-05-03'),
(4, 4, 4, 20000000.00, '2025-05-04'),
(5, 5, 5, 25000000.00, '2025-05-05');

-- --------------------------------------------------------

--
-- Table structure for table `resale_properties`
--

CREATE TABLE `resale_properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `status` enum('available','sold','inactive') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resale_properties`
--

INSERT INTO `resale_properties` (`id`, `owner_id`, `details`, `price`, `status`) VALUES
(1, 1, 'Value for details 1', 15000000.00, ''),
(2, 2, 'Value for details 2', 7000000.00, ''),
(3, 3, 'Value for details 3', 9000000.00, ''),
(4, 4, 'Value for details 4', 20000000.00, ''),
(5, 5, 'Value for details 5', 25000000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `resell_commission_structure`
--

CREATE TABLE `resell_commission_structure` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `property_category` enum('plot','flat','house','commercial','land') NOT NULL,
  `min_value` decimal(15,2) NOT NULL,
  `max_value` decimal(15,2) NOT NULL,
  `commission_percentage` decimal(5,2) NOT NULL,
  `fixed_commission` decimal(10,2) DEFAULT 0.00,
  `commission_type` enum('percentage','fixed','both') DEFAULT 'percentage'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resell_commission_structure`
--

INSERT INTO `resell_commission_structure` (`id`, `plan_id`, `property_category`, `min_value`, `max_value`, `commission_percentage`, `fixed_commission`, `commission_type`) VALUES
(1, 1, 'plot', 0.00, 10000000.00, 3.00, 0.00, 'percentage'),
(2, 1, 'plot', 10000000.00, 50000000.00, 4.00, 0.00, 'percentage'),
(3, 1, 'plot', 50000000.00, 999999999.00, 5.00, 0.00, 'percentage'),
(4, 1, 'flat', 0.00, 50000000.00, 2.00, 0.00, 'percentage'),
(5, 1, 'flat', 50000000.00, 999999999.00, 3.00, 0.00, 'percentage'),
(6, 1, 'house', 0.00, 999999999.00, 3.00, 0.00, 'percentage'),
(7, 1, 'commercial', 0.00, 999999999.00, 4.00, 0.00, 'percentage'),
(8, 1, 'land', 0.00, 999999999.00, 2.00, 0.00, 'percentage');

-- --------------------------------------------------------

--
-- Table structure for table `reward_history`
--

CREATE TABLE `reward_history` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `reward_type` varchar(50) DEFAULT NULL,
  `reward_value` decimal(12,2) DEFAULT NULL,
  `reward_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward_history`
--

INSERT INTO `reward_history` (`id`, `associate_id`, `reward_type`, `reward_value`, `reward_date`, `description`, `created_at`) VALUES
(1, 1, 'villa', 1000.00, '2025-05-01', 'Beautiful luxury villa with garden and pool', '2025-04-30 18:30:00'),
(2, 2, 'apartment', 2000.00, '2025-05-07', 'Modern apartment in city center with great amenities', '2025-05-06 18:30:00'),
(3, 3, 'house', 3000.00, '2025-05-13', 'Spacious family home in quiet neighborhood', '2025-05-12 18:30:00'),
(4, 4, 'villa', 4000.00, '2025-05-19', 'Beachfront luxury home with amazing views', '2025-05-18 18:30:00'),
(5, 5, 'penthouse', 5000.00, '2025-05-25', 'Luxury penthouse with terrace and city views', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(2, 'Admin'),
(21, 'ceo'),
(23, 'cfo'),
(25, 'cm'),
(24, 'coo'),
(22, 'cto'),
(20, 'director'),
(10, 'finance'),
(13, 'hr'),
(12, 'it_head'),
(16, 'legal'),
(19, 'manager'),
(14, 'marketing'),
(26, 'office_admin'),
(27, 'official_employee'),
(15, 'operations'),
(17, 'sales'),
(9, 'superadmin'),
(18, 'support');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_approvals`
--

CREATE TABLE `role_change_approvals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `action` enum('assign','remove') NOT NULL,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_change_approvals`
--

INSERT INTO `role_change_approvals` (`id`, `user_id`, `role_id`, `action`, `requested_by`, `status`, `requested_at`, `decided_by`, `decided_at`) VALUES
(1, 1, 1, 'remove', 1, '', '2025-05-01 00:00:00', 1, '2025-05-01 00:00:00'),
(2, 2, 2, 'assign', 2, 'pending', '2025-05-07 00:00:00', 2, '2025-05-07 00:00:00'),
(3, 3, 3, 'remove', 3, '', '2025-05-13 00:00:00', 3, '2025-05-13 00:00:00'),
(4, 4, 4, 'assign', 4, '', '2025-05-19 00:00:00', 4, '2025-05-19 00:00:00'),
(5, 5, 5, 'remove', 5, '', '2025-05-25 00:00:00', 5, '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `saas_instances`
--

CREATE TABLE `saas_instances` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saas_instances`
--

INSERT INTO `saas_instances` (`id`, `client_name`, `domain`, `status`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for domain 1', 'active', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for domain 2', 'pending', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for domain 3', 'completed', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for domain 4', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for domain 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `paid_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salaries`
--

INSERT INTO `salaries` (`id`, `employee_id`, `month`, `year`, `amount`, `status`, `paid_on`) VALUES
(1, 10, 3, 2025, 50000.00, 'paid', '2025-03-31'),
(2, 11, 3, 2025, 30000.00, 'paid', '2025-03-31'),
(3, 1, 1, 1, 15000000.00, '', '2025-05-01'),
(4, 2, 2, 2, 7000000.00, 'pending', '2025-05-02'),
(5, 3, 3, 3, 9000000.00, '', '2025-05-03'),
(6, 4, 4, 4, 20000000.00, '', '2025-05-04'),
(7, 5, 5, 5, 25000000.00, '', '2025-05-05');

-- --------------------------------------------------------

--
-- Table structure for table `salary_plan`
--

CREATE TABLE `salary_plan` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `salary_amount` decimal(12,2) DEFAULT NULL,
  `payout_date` date DEFAULT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_plan`
--

INSERT INTO `salary_plan` (`id`, `associate_id`, `level`, `salary_amount`, `payout_date`, `status`, `created_at`) VALUES
(1, 1, 1, 15000000.00, '2025-05-01', '', '2025-04-30 18:30:00'),
(2, 2, 2, 7000000.00, '2025-05-07', 'pending', '2025-05-06 18:30:00'),
(3, 3, 3, 9000000.00, '2025-05-13', '', '2025-05-12 18:30:00'),
(4, 4, 4, 20000000.00, '2025-05-19', '', '2025-05-18 18:30:00'),
(5, 5, 5, 25000000.00, '2025-05-25', '', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL,
  `payment_terms` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','sent','paid','partial','overdue','cancelled') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoice_items`
--

CREATE TABLE `sales_invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_searches`
--

CREATE TABLE `saved_searches` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `search_params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_metadata`
--

CREATE TABLE `seo_metadata` (
  `id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` varchar(50) DEFAULT 'index, follow',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seo_metadata`
--

INSERT INTO `seo_metadata` (`id`, `page_name`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`, `og_image`, `canonical_url`, `robots`, `created_at`, `updated_at`) VALUES
(1, 'home', 'APS Dream Homes - Leading Real Estate Developer in Gorakhpur', 'Find your dream property with APS Dream Homes. Premium residential and commercial properties in Gorakhpur, UP with modern amenities.', 'real estate gorakhpur, property gorakhpur, flats gorakhpur, apartments gorakhpur, commercial property up', 'APS Dream Homes - Premium Properties', 'Discover amazing properties in Gorakhpur', NULL, NULL, 'index, follow', '2025-09-30 15:54:54', '2025-09-30 15:54:54'),
(2, 'properties', 'Properties for Sale - APS Dream Homes Gorakhpur', 'Browse our exclusive collection of residential and commercial properties in Gorakhpur. Find apartments, villas, and commercial spaces.', 'properties gorakhpur, flats for sale, apartments gorakhpur, commercial property', 'Properties - APS Dream Homes', 'Find your perfect property', NULL, NULL, 'index, follow', '2025-09-30 15:54:54', '2025-09-30 15:54:54');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `total_area` decimal(10,2) NOT NULL COMMENT 'in acres',
  `developed_area` decimal(10,2) DEFAULT 0.00,
  `site_type` enum('residential','commercial','mixed','industrial') DEFAULT 'residential',
  `status` enum('planning','under_development','active','completed','inactive') DEFAULT 'planning',
  `manager_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'footer_content', 'Your trusted partner in real estate, providing quality properties and excellent service across India.', '2025-05-06 05:43:21', '2025-05-06 05:43:21'),
(2, 'footer_links', '[{\"url\":\"/\",\"text\":\"Home\"},{\"url\":\"/properties.php\",\"text\":\"Properties\"},{\"url\":\"/about.php\",\"text\":\"About\"},{\"url\":\"/contact.php\",\"text\":\"Contact\"}]', '2025-05-06 05:43:21', '2025-05-06 05:43:21'),
(3, 'social_links', '[{\"url\":\"https://facebook.com/apsdreamhomes\",\"icon\":\"fa-facebook\"},{\"url\":\"https://twitter.com/apsdreamhomes\",\"icon\":\"fa-twitter\"},{\"url\":\"https://instagram.com/apsdreamhomes\",\"icon\":\"fa-instagram\"}]', '2025-05-06 05:43:21', '2025-05-06 05:43:21'),
(4, 'header_menu_items', '[{\"url\":\"/apsdreamhomefinal/\",\"text\":\"Home\"},{\"url\":\"/apsdreamhomefinal/project.php\",\"text\":\"Project\"},{\"url\":\"/apsdreamhomefinal/about.php\",\"text\":\"About\"},{\"url\":\"/apsdreamhomefinal/contact.php\",\"text\":\"Contact\"},{\"url\":\"/apsdreamhomefinal/login.php\",\"text\":\"Login\"}]', '2025-05-12 13:47:11', '2025-05-12 13:47:11'),
(5, 'site_logo', '/apsdreamhomefinal/assets/images/logo.png', '2025-05-12 13:47:11', '2025-05-12 13:47:11'),
(6, 'header_styles', '{\"background\":\"#ffffff\",\"text_color\":\"#333333\"}', '2025-05-12 13:47:11', '2025-05-12 13:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `smart_contracts`
--

CREATE TABLE `smart_contracts` (
  `id` int(11) NOT NULL,
  `agreement_name` varchar(255) NOT NULL,
  `parties` varchar(255) DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `blockchain_txn` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smart_contracts`
--

INSERT INTO `smart_contracts` (`id`, `agreement_name`, `parties`, `terms`, `status`, `blockchain_txn`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for parties 1', 'Value for terms 1', 'active', 'Value for blockchain_txn 1', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for parties 2', 'Value for terms 2', 'pending', 'Value for blockchain_txn 2', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for parties 3', 'Value for terms 3', 'completed', 'Value for blockchain_txn 3', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for parties 4', 'Value for terms 4', 'cancelled', 'Value for blockchain_txn 4', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for parties 5', 'Value for terms 5', 'active', 'Value for blockchain_txn 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `social_media_links`
--

CREATE TABLE `social_media_links` (
  `id` int(11) NOT NULL,
  `platform_name` varchar(50) NOT NULL,
  `platform_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_media_links`
--

INSERT INTO `social_media_links` (`id`, `platform_name`, `platform_url`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Facebook', 'https://www.facebook.com/apsdreamhomes', 1, 1, '2025-09-30 15:54:26'),
(2, 'Instagram', 'https://www.instagram.com/apsdreamhomes', 1, 2, '2025-09-30 15:54:26'),
(3, 'LinkedIn', 'https://www.linkedin.com/company/aps-dream-homes', 1, 3, '2025-09-30 15:54:26'),
(4, 'YouTube', 'https://www.youtube.com/channel/apsdreamhomes', 1, 4, '2025-09-30 15:54:26');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `credit_days` int(11) DEFAULT 0,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `total_purchases` decimal(15,2) DEFAULT 0.00,
  `total_payments` decimal(15,2) DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `supplier_type` enum('material','service','both') DEFAULT 'material',
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Value for subject 1', 'This is a sample message for record 1.', 'active', '2025-04-30 18:30:00'),
(2, 2, 'Value for subject 2', 'This is a sample message for record 2.', 'pending', '2025-05-06 18:30:00'),
(3, 3, 'Value for subject 3', 'This is a sample message for record 3.', 'completed', '2025-05-12 18:30:00'),
(4, 4, 'Value for subject 4', 'This is a sample message for record 4.', 'cancelled', '2025-05-18 18:30:00'),
(5, 5, 'Value for subject 5', 'This is a sample message for record 5.', 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `table_name`
--

CREATE TABLE `table_name` (
  `id` int(11) NOT NULL COMMENT 'Primary Key',
  `create_time` datetime DEFAULT NULL COMMENT 'Create Time',
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `assigned_by`, `due_date`, `status`, `created_at`) VALUES
(1, 'Luxury Villa', 'Beautiful luxury villa with garden and pool', 1, 1, '2025-05-01', '', '2025-04-30 18:30:00'),
(2, 'City Apartment', 'Modern apartment in city center with great amenities', 2, 2, '2025-05-07', 'pending', '2025-05-06 18:30:00'),
(3, 'Suburban House', 'Spacious family home in quiet neighborhood', 3, 3, '2025-05-13', 'completed', '2025-05-12 18:30:00'),
(4, 'Beach Property', 'Beachfront luxury home with amazing views', 4, 4, '2025-05-19', 'cancelled', '2025-05-18 18:30:00'),
(5, 'Penthouse', 'Luxury penthouse with terrace and city views', 5, 5, '2025-05-25', '', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `name`, `designation`, `bio`, `photo`, `status`, `created_at`) VALUES
(1, 'Amit Singh', 'Managing Director', 'Over 20 years experience in real estate and project management.', '/assets/images/team/amit.jpg', 'active', '2025-04-30 06:28:57'),
(2, 'Neha Verma', 'Sales Head', 'Expert in customer relations and property sales.', '/assets/images/team/neha.jpg', 'active', '2025-04-30 06:28:57'),
(3, 'Rahul Pandey', 'Project Engineer', 'Specialist in construction and site supervision.', '/assets/images/team/rahul.jpg', 'active', '2025-04-30 06:28:57'),
(4, 'Rahul Sharma', 'Value for designation 1', 'Value for bio 1', 'Value for photo 1', 'active', '2025-04-30 18:30:00'),
(5, 'Priya Singh', 'Value for designation 2', 'Value for bio 2', 'Value for photo 2', '', '2025-05-06 18:30:00'),
(6, 'Amit Kumar', 'Value for designation 3', 'Value for bio 3', 'Value for photo 3', '', '2025-05-12 18:30:00'),
(7, 'Neha Patel', 'Value for designation 4', 'Value for bio 4', 'Value for photo 4', '', '2025-05-18 18:30:00'),
(8, 'Vikram Mehta', 'Value for designation 5', 'Value for bio 5', 'Value for photo 5', 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `team_hierarchy`
--

CREATE TABLE `team_hierarchy` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_hierarchy`
--

INSERT INTO `team_hierarchy` (`id`, `associate_id`, `upline_id`, `level`, `created_at`) VALUES
(1, 1, 1, 1, '2025-04-30 18:30:00'),
(2, 2, 2, 2, '2025-05-06 18:30:00'),
(3, 3, 3, 3, '2025-05-12 18:30:00'),
(4, 4, 4, 4, '2025-05-18 18:30:00'),
(5, 5, 5, 5, '2025-05-24 18:30:00'),
(6, 5, 1, 2, '2025-04-02 19:32:32'),
(7, 6, 4, 1, '2025-04-02 19:34:06'),
(8, 6, 2, 2, '2025-04-02 19:34:06'),
(9, 6, 1, 3, '2025-04-02 19:34:06'),
(11, 7, 6, 1, '2025-04-02 19:35:00'),
(12, 7, 4, 2, '2025-04-02 19:35:00'),
(13, 7, 2, 3, '2025-04-02 19:35:00'),
(14, 7, 1, 4, '2025-04-02 19:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `expertise` varchar(255) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `bio`, `photo`, `email`, `phone`, `linkedin`, `expertise`, `experience`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rajesh Kumar', 'Senior Legal Advisor', '15+ years of experience in property law and real estate documentation.', 'team1.jpg', 'rajesh@apsdreamhome.com', '+91-9876543210', 'linkedin.com/rajeshkumar', 'Property Law, Documentation', '15 years', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(2, 'Priya Sharma', 'Legal Documentation Expert', 'Specialized in property registration and title verification processes.', 'team2.jpg', 'priya@apsdreamhome.com', '+91-9876543211', 'linkedin.com/priyasharma', 'Title Verification, Registration', '8 years', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11'),
(3, 'Amit Singh', 'Legal Consultant', 'Expert in dispute resolution and property agreement drafting.', 'team3.jpg', 'amit@apsdreamhome.com', '+91-9876543212', 'linkedin.com/amitsingh', 'Dispute Resolution, Agreements', '12 years', 0, 'active', '2025-10-07 18:29:11', '2025-10-07 18:29:11');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `testimonial` text NOT NULL,
  `client_photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','active','inactive') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `email`, `rating`, `testimonial`, `client_photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ravi Kumar', NULL, 5, 'APS Dream Homes helped me find my dream house in Gorakhpur. Highly recommended!', '/assets/images/testimonials/ravi.jpg', 'approved', '2025-04-30 06:25:53', '2025-05-28 18:36:01'),
(2, 'Priya Sharma', NULL, 5, 'Professional team and transparent process. Very happy with my new property!', '/assets/images/testimonials/priya.jpg', 'approved', '2025-04-30 06:25:53', '2025-05-28 18:36:01'),
(3, '', NULL, 5, '', NULL, 'approved', '2025-05-17 12:21:22', '2025-05-28 18:36:01'),
(4, 'Rahul Sharma', NULL, 5, 'Value for testimonial 1', 'Value for client_photo 1', 'approved', '2025-04-30 18:30:00', '2025-05-28 18:36:01'),
(5, 'Priya Singh', NULL, 5, 'Value for testimonial 2', 'Value for client_photo 2', '', '2025-05-06 18:30:00', '2025-05-28 18:36:01'),
(6, 'Amit Kumar', NULL, 5, 'Value for testimonial 3', 'Value for client_photo 3', '', '2025-05-12 18:30:00', '2025-05-28 18:36:01'),
(7, 'Neha Patel', NULL, 5, 'Value for testimonial 4', 'Value for client_photo 4', '', '2025-05-18 18:30:00', '2025-05-28 18:36:01'),
(8, 'Vikram Mehta', NULL, 5, 'Value for testimonial 5', 'Value for client_photo 5', 'approved', '2025-05-24 18:30:00', '2025-05-28 18:36:01'),
(9, 'raju', 'raju345@gmail.com', 1, 'Turant ragistry Turant kabja milta hai', '<div class=\"avatar-circle\" style=\"background: #7e3e2d; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24px;\">R</div>', 'pending', '2025-05-28 19:51:58', '2025-05-28 19:51:58'),
(10, 'Anita Singh', 'anita@example.com', 5, 'Excellent service! Found my dream home within a week. Highly recommended!', NULL, 'active', '2025-09-26 20:19:25', '2025-09-26 20:19:25'),
(11, 'Vikram Mehta', 'vikram@example.com', 5, 'Professional team with great market knowledge. Smooth transaction process.', NULL, 'active', '2025-09-26 20:19:25', '2025-09-26 20:19:25'),
(12, 'Kavita Jain', 'kavita@example.com', 5, 'Outstanding experience! The agents were very helpful and patient throughout.', NULL, 'active', '2025-09-26 20:19:25', '2025-09-26 20:19:25');

-- --------------------------------------------------------

--
-- Table structure for table `third_party_integrations`
--

CREATE TABLE `third_party_integrations` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `api_token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `third_party_integrations`
--

INSERT INTO `third_party_integrations` (`id`, `type`, `api_token`, `created_at`) VALUES
(1, 'villa', 'Value for api_token 1', '2025-05-01 00:00:00'),
(2, 'apartment', 'Value for api_token 2', '2025-05-07 00:00:00'),
(3, 'house', 'Value for api_token 3', '2025-05-13 00:00:00'),
(4, 'villa', 'Value for api_token 4', '2025-05-19 00:00:00'),
(5, 'penthouse', 'Value for api_token 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ref_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `date`, `description`, `ref_id`, `created_at`, `updated_at`) VALUES
(1, 4, 'Booking', 1500000.00, '2025-01-22', 'Plot booking Green Valley', 'TXN001', '2025-05-24 20:06:37', '2025-05-17 20:06:37'),
(2, 5, 'Booking', 2500000.00, '2024-12-08', 'Flat booking Green Valley', 'TXN002', '2025-05-24 20:06:37', '2025-05-17 20:06:37'),
(3, 6, 'Booking', 1500000.00, '2025-02-11', 'Plot booking Green Valley', 'TXN003', '2025-05-24 20:06:37', '2025-05-17 20:06:37'),
(4, 1, 'booking', 2500000.00, NULL, NULL, NULL, '2025-05-17 20:06:37', '2025-05-07 20:06:37'),
(5, 2, 'booking', 3200000.00, NULL, NULL, NULL, '2025-05-18 20:06:37', '2025-05-05 20:06:37'),
(6, 3, 'booking', 1800000.00, NULL, NULL, NULL, '2025-05-19 20:06:37', '2025-05-13 20:06:37'),
(7, 4, 'booking', 4000000.00, NULL, NULL, NULL, '2025-05-19 20:06:37', '2025-05-06 20:06:37'),
(8, 5, 'booking', 2700000.00, NULL, NULL, NULL, '2025-05-20 20:06:37', '2025-05-16 20:06:37'),
(9, 1, 'booking', 2500000.00, '2025-05-18', 'Booking for Property 201', 'BK20250415A', '2025-05-17 20:06:37', '2025-05-17 20:06:37'),
(10, 2, 'booking', 3200000.00, '2025-03-06', 'Booking for Property 202', 'BK20250416B', '2025-05-18 20:06:37', '2025-05-17 20:06:37'),
(11, 3, 'booking', 1800000.00, '2025-05-14', 'Booking for Property 203', 'BK20250417C', '2025-05-19 20:06:37', '2025-05-17 20:06:37'),
(12, 4, 'booking', 4000000.00, '2024-12-06', 'Booking for Property 204', 'BK20250417D', '2025-05-19 20:06:37', '2025-05-17 20:06:37'),
(13, 5, 'booking', 2700000.00, '2025-02-23', 'Booking for Property 205', 'BK20250418E', '2025-05-20 20:06:37', '2025-05-17 20:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `upload_audit_log`
--

CREATE TABLE `upload_audit_log` (
  `id` int(11) NOT NULL,
  `event_type` varchar(64) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_table` varchar(64) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `drive_file_id` varchar(128) DEFAULT NULL,
  `uploader` varchar(128) NOT NULL,
  `slack_status` varchar(32) DEFAULT NULL,
  `telegram_status` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `upload_audit_log`
--

INSERT INTO `upload_audit_log` (`id`, `event_type`, `entity_id`, `entity_table`, `file_name`, `drive_file_id`, `uploader`, `slack_status`, `telegram_status`, `created_at`) VALUES
(1, 'villa', 1, 'Value for entity_table 1', 'Rahul Sharma', '1', 'Value for uploader 1', 'active', 'active', '2025-04-30 18:30:00'),
(2, 'apartment', 2, 'Value for entity_table 2', 'Priya Singh', '2', 'Value for uploader 2', 'pending', 'pending', '2025-05-06 18:30:00'),
(3, 'house', 3, 'Value for entity_table 3', 'Amit Kumar', '3', 'Value for uploader 3', 'completed', 'completed', '2025-05-12 18:30:00'),
(4, 'villa', 4, 'Value for entity_table 4', 'Neha Patel', '4', 'Value for uploader 4', 'cancelled', 'cancelled', '2025-05-18 18:30:00'),
(5, 'penthouse', 5, 'Value for entity_table 5', 'Vikram Mehta', '5', 'Value for uploader 5', 'active', 'active', '2025-05-24 18:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `api_access` tinyint(1) DEFAULT 0,
  `api_rate_limit` int(11) DEFAULT 1000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_picture`, `phone`, `type`, `password`, `status`, `created_at`, `updated_at`, `api_access`, `api_rate_limit`) VALUES
(1, 'Super Admin', 'superadmin@dreamhome.com', NULL, '9999999999', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(2, 'Agent One', 'agent1@dreamhome.com', NULL, '9000000001', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(3, 'Builder One', 'builder1@dreamhome.com', NULL, '9000000002', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(4, 'Customer One', 'customer1@dreamhome.com', NULL, '9000000003', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:37:15', '2025-05-16 20:57:55', 0, 1000),
(5, 'Customer Two', 'customer2@dreamhome.com', NULL, '9000000004', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:45:19', '2025-05-16 20:57:55', 0, 1000),
(6, 'Customer Three', 'customer3@dreamhome.com', NULL, '9000000005', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:45:19', '2025-05-16 20:57:55', 0, 1000),
(7, 'Associate One', 'associate1@dreamhome.com', NULL, '9000000006', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:45:19', '2025-05-16 20:57:55', 0, 1000),
(8, 'Michael', 'michael@mail.com', NULL, '8542221140', NULL, '6812f136d636e737248d365016f8cfd5139e387c', 'active', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 0, 1000),
(9, 'APS Dream Homes', 'apsdreamhomes44@gmail.com', NULL, '', NULL, '', 'active', '2025-03-24 21:18:04', '2025-03-25 19:14:38', 0, 1000),
(10, 'Customer Four', 'customer4@dreamhome.com', NULL, '9000000007', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:52:02', '2025-05-16 20:57:55', 0, 1000),
(11, 'Employee One', 'employee1@dreamhome.com', NULL, '9000000009', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:54:15', '2025-05-16 20:57:55', 0, 1000),
(12, 'Employee Two', 'employee2@dreamhome.com', NULL, '9000000010', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:54:15', '2025-05-16 20:57:55', 0, 1000),
(13, 'Farmer One', 'farmer1@dreamhome.com', NULL, '9000000011', NULL, '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-21 20:54:15', '2025-05-16 20:57:55', 0, 1000),
(27, 'raju rajpoot', 'raju556767@gmail.com', NULL, '7897787656', NULL, '$2y$10$NZMXbAuf5N1VGgFlx0BcT.CXUhvXNfg2emBAz1dtZOB5n0tugpYLW', 'active', '2025-03-29 20:25:04', '2025-03-29 20:25:04', 0, 1000),
(32, 'raju rajpoot', 'rajut5456323767@gmail.com', NULL, '7897765556', NULL, '$2y$10$tgXpRAsQtDeF.EvnSZ7gyOj0EbtU14u/YOVmuTPyFQdtXiOTRs7Li', 'active', '2025-03-29 20:57:10', '2025-03-29 20:57:10', 0, 1000),
(33, 'raju rajpoot singh', 'rajut54563253767@gmail.com', NULL, '7897565656', NULL, '$2y$10$.yX9Xp55uVPGt0V9.9hX6eJw749hy8ZD4wt3XFbrHItM2QT0nOCm6', 'active', '2025-04-01 18:05:40', '2025-04-01 18:05:40', 0, 1000),
(34, 'ruhitwo', 'ruhi2@gmail.com', NULL, '5466776435', NULL, '$2y$10$dh.42xn.6d8AKfYsFaxjlediKrfkFPe5GuBBs3vC.AoVwcZ9aUrYm', 'active', '2025-04-02 19:30:27', '2025-04-02 19:30:27', 0, 1000),
(35, 'ruhithree', 'ruhi3@gmail.com', NULL, '8788776787', NULL, '$2y$10$o8FlNyDEXuXXVWCsxGK5L.1wlMTktOSs6oudAOA/Rd2faKl4QpIFK', 'active', '2025-04-02 19:32:32', '2025-04-02 19:32:32', 0, 1000),
(36, 'ruhifour', 'ruhi4@gmail.com', NULL, '8779787787', NULL, '$2y$10$bUo2ZhK4KZ7xToKaOWXcaO1zxgomTHAb51r357nq0FMBv3ePZ2ofy', 'active', '2025-04-02 19:34:06', '2025-04-02 19:34:06', 0, 1000),
(37, 'ruhisix', 'ruhi6@gmail.com', NULL, '6764543654', NULL, '$2y$10$ZenpMb0wtdzJ.4o1EKOpwuBCEyERDzTr4C3yRTY4LZg9OhIbe9lSC', 'active', '2025-04-02 19:35:00', '2025-04-02 19:35:00', 0, 1000),
(76, 'APS', 'test@test.com', NULL, '0000000000', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-08 20:40:23', '2025-05-16 20:57:55', 0, 1000),
(77, 'Abhay Singh', 'techguruabhay@gmail.com', NULL, '7007444842', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-15 19:07:10', '2025-05-16 20:57:55', 0, 1000),
(78, 'Pravin kumar Prabhat', 'pravin.prabhat@yahoo.com', NULL, '9026336001', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-16 01:37:20', '2025-05-16 20:57:55', 0, 1000),
(79, 'Anita Singh', 'rudra.vir007@gmail.com', NULL, '7068013668', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-16 01:39:27', '2025-05-16 20:57:55', 0, 1000),
(80, 'Anuj kumar srivastava', 'devsrivastava74@gmail.com', NULL, '8707742187', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-16 06:16:34', '2025-05-16 20:57:55', 0, 1000),
(81, 'Rachna gupta', 'rachana@gmail.com', NULL, '7007414234', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-17 03:40:25', '2025-05-16 20:57:55', 0, 1000),
(82, 'Puneet kumar sinha', 'puneetsinha123@gmail.com', NULL, '9935883444', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:03:38', '2025-05-16 20:57:55', 0, 1000),
(83, 'Rahul Verma', 'rv83810@gmail.com', NULL, '8858763451', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:25:31', '2025-05-16 20:57:55', 0, 1000),
(84, 'Neeraj Kumar Singh', 'nerajsingh235@gmail.com', NULL, '9506362690', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:37:13', '2025-05-16 20:57:55', 0, 1000),
(85, 'Pramod kumar sharma', 'Pramod.rich1989@gmail.com', NULL, '8318037728', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-20 10:48:22', '2025-05-16 20:57:55', 0, 1000),
(86, 'ashok kumar', 'ashok12@gmail.com', NULL, '8808403728', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:42:30', '2025-05-16 20:57:55', 0, 1000),
(87, 'rinku chauhan', 'rinku@gmail.com', NULL, '9219494408', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:47:51', '2025-05-16 20:57:55', 0, 1000),
(88, 'SUNIL KUMAR', 'sunil12@gamil.com', NULL, '7348127038', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:49:43', '2025-05-16 20:57:55', 0, 1000),
(89, 'irfan ahmad', 'irfan12@gmail.com', NULL, '8318431354', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:53:49', '2025-05-16 20:57:55', 0, 1000),
(90, 'priya mishra', 'priyag.9671@gmail.com', NULL, '9140640713', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 09:54:28', '2025-05-16 20:57:55', 0, 1000),
(91, 'Rishikesh', 'rishi12@gmail.com', NULL, '8172938998', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:11:01', '2025-05-16 20:57:55', 0, 1000),
(92, 'rajni verma', 'rajniskn@gmail.com', NULL, '9956390332', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:11:45', '2025-05-16 20:57:55', 0, 1000),
(93, 'Rahul kannujiya', 'rahul12@gmail.com', NULL, '7607952353', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:14:25', '2025-05-16 20:57:55', 0, 1000),
(94, 'anuradha rai', 'anuradharai@gmail.com', NULL, '7678767656', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:15:00', '2025-05-16 20:57:55', 0, 1000),
(95, 'avinash pandey', 'avinash12@gmail.com', NULL, '9517241234', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:17:33', '2025-05-16 20:57:55', 0, 1000),
(96, 'avinash tiwari', 'tiwari1210@gmail.com', NULL, '8318721419', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:18:30', '2025-05-16 20:57:55', 0, 1000),
(97, 'shalu bharti', 'shalubharti@gmail.com', NULL, '9792398767', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:20:24', '2025-05-16 20:57:55', 0, 1000),
(98, 'anshika upadhyay', 'itsanshika454@gmail.com', NULL, '7754831158', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:21:46', '2025-05-16 20:57:55', 0, 1000),
(99, 'priyanka yadav', 'priyakayadav@gmail.com', NULL, '7607702191', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-09-21 10:23:41', '2025-05-16 20:57:55', 0, 1000),
(100, 'maggi', 'maggi@gmail.com', NULL, '6765676566', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-05 06:39:42', '2025-05-16 20:57:55', 0, 1000),
(101, 'abhay', 'abhay444@gmail.com', NULL, '6576646546', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-07 22:01:30', '2025-05-16 20:57:55', 0, 1000),
(102, 'abhay', 'anjuuuuu@gmail.com', NULL, '6565654345', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-08 14:37:23', '2025-05-16 20:57:55', 0, 1000),
(103, 'builder', 'builder@gmail.com', NULL, '3764765456', 'builder', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 17:35:07', '2025-05-16 20:57:55', 0, 1000),
(104, 'user', 'user@gmail.com', NULL, '4553767598', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 17:35:33', '2025-05-16 20:57:55', 0, 1000),
(105, 'agent', 'agent@gmail.com', NULL, '5546745554', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 17:44:21', '2025-05-16 20:57:55', 0, 1000),
(106, 'anuj22', 'anuj7656@gmail.com', NULL, '7898786566', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-09 20:24:14', '2025-05-16 20:57:55', 0, 1000),
(107, 'Abhay kumar singh', 'techgure434hjhuabhay@gmail.com', NULL, '7004499842', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-15 10:32:39', '2025-05-16 20:57:55', 0, 1000),
(108, 'Rohit', 'rohit123@gmail.com', NULL, '1234556786', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-10-15 14:00:32', '2025-05-16 20:57:55', 0, 1000),
(109, 'abhayy', 'abhayy3007@gmail.com', NULL, '5656787676', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2024-11-08 07:33:33', '2025-05-16 20:57:55', 0, 1000),
(110, 'praveen', 'praveen@gmail.com', NULL, '676765665', 'assosiate', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-02-27 11:14:03', '2025-05-16 20:57:55', 0, 1000),
(139, 'admin', 'abhay3007@live.com', NULL, '07007444842', 'admin', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-24 18:30:00', '2025-05-16 20:57:55', 0, 1000),
(140, 'Rahul Sharma', 'rahul@example.com', NULL, '9876543210', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(141, 'Priya Singh', 'priya@example.com', NULL, '8765432109', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(142, 'Amit Kumar', 'amit@example.com', NULL, '7654321098', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(143, 'Neha Gupta', 'neha@example.com', NULL, '6543210987', 'builder', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(144, 'Vikram Patel', 'vikram@example.com', NULL, '5432109876', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(145, 'Demo User 1', 'demo.user1@aps.com', NULL, '9000010001', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(146, 'Demo User 2', 'demo.user2@aps.com', NULL, '9000010002', 'user', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(147, 'Demo Agent 1', 'demo.agent1@aps.com', NULL, '9000020001', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(148, 'Demo Agent 2', 'demo.agent2@aps.com', NULL, '9000020002', 'agent', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(149, 'Demo Builder', 'demo.builder@aps.com', NULL, '9000030001', 'builder', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(150, 'Demo Customer', 'demo.customer@aps.com', NULL, '9000040001', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$Z2JQeDFhNTB1QUxxNjdaWQ$6vNrWDfkW+Oof5xzcY+TIXZuithNg42wwH39zTngyOI', 'active', '2025-04-23 01:25:34', '2025-09-26 08:39:01', 0, 1000),
(151, 'Demo Investor', 'demo.investor@aps.com', NULL, '9000040002', 'investor', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(152, 'Demo Tenant', 'demo.tenant@aps.com', NULL, '9000040003', 'tenant', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:25:34', '2025-05-16 20:57:55', 0, 1000),
(166, 'Customer User', 'customer@demo.com', NULL, '7000000001', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$Z2JQeDFhNTB1QUxxNjdaWQ$6vNrWDfkW+Oof5xzcY+TIXZuithNg42wwH39zTngyOI', 'active', '2025-04-23 01:26:09', '2025-09-26 08:39:01', 0, 1000),
(167, 'Investor User', 'investor@demo.com', NULL, '7000000002', 'investor', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:26:09', '2025-05-16 20:57:55', 0, 1000),
(168, 'Tenant User', 'tenant@demo.com', NULL, '7000000003', 'tenant', '$argon2id$v=19$m=65536,t=4,p=1$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas', 'active', '2025-04-23 01:26:09', '2025-05-16 20:57:55', 0, 1000),
(184, 'Customer One', 'customer1@example.com', NULL, '9876532101', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$Z2JQeDFhNTB1QUxxNjdaWQ$6vNrWDfkW+Oof5xzcY+TIXZuithNg42wwH39zTngyOI', 'active', '2024-01-10 04:30:00', '2025-09-26 08:39:01', 0, 1000),
(185, 'Customer Two', 'customer2@example.com', NULL, '9876532102', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$Z2JQeDFhNTB1QUxxNjdaWQ$6vNrWDfkW+Oof5xzcY+TIXZuithNg42wwH39zTngyOI', 'active', '2024-01-15 06:00:00', '2025-09-26 08:39:01', 0, 1000),
(186, 'Customer Three', 'customer3@example.com', NULL, '9876532103', 'customer', '$argon2id$v=19$m=65536,t=4,p=1$Z2JQeDFhNTB1QUxxNjdaWQ$6vNrWDfkW+Oof5xzcY+TIXZuithNg42wwH39zTngyOI', 'active', '2024-02-05 08:45:00', '2025-09-26 08:39:01', 0, 1000),
(187, 'Customer Four', 'customer4@example.com', NULL, '9876532104', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-02-20 11:00:00', '2025-09-24 22:18:29', 0, 1000),
(188, 'Customer Five', 'customer5@example.com', NULL, '9876532105', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-03-01 04:15:00', '2025-09-24 22:18:29', 0, 1000),
(209, 'Customer One', 'customer6@example.com', NULL, '9874532101', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-01-10 04:30:00', '2025-09-24 22:29:20', 0, 1000),
(210, 'Customer Two', 'customer7@example.com', NULL, '987232102', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-01-15 06:00:00', '2025-09-24 22:29:20', 0, 1000),
(211, 'Customer Three', 'customer8@example.com', NULL, '9676532103', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-02-05 08:45:00', '2025-09-24 22:29:20', 0, 1000),
(212, 'Customer Four', 'customer9@example.com', NULL, '9898532104', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-02-20 11:00:00', '2025-09-24 22:29:20', 0, 1000),
(213, 'Customer Five', 'customer10@example.com', NULL, '98745932105', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2024-03-01 04:15:00', '2025-09-24 22:29:20', 0, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `preference_key`, `preference_value`, `created_at`, `updated_at`) VALUES
(1, 76, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(2, 139, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(3, 108, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(4, 103, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(5, 104, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(6, 144, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(7, 105, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(8, 109, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(9, 143, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(10, 102, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(11, 101, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(12, 100, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(13, 110, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(14, 166, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(15, 167, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(16, 168, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(17, 107, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(18, 81, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(19, 77, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(20, 79, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(21, 88, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(22, 99, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(23, 93, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(24, 142, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(25, 94, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(26, 98, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(27, 106, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(28, 91, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(29, 85, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(30, 89, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(31, 96, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(32, 80, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(33, 141, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(34, 86, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(35, 83, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(36, 2, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(37, 3, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(38, 4, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(39, 5, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(40, 6, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(41, 7, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(42, 10, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(43, 11, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(44, 12, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(45, 13, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(46, 145, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(47, 146, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(48, 147, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(49, 148, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(50, 149, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(51, 150, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(52, 151, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(53, 152, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(54, 78, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(55, 90, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(56, 87, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(57, 84, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(58, 95, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(59, 97, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(60, 140, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(61, 82, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(62, 92, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05'),
(63, 1, 'notification_preferences', '{\"email\":true,\"in_app\":true,\"sms\":false}', '2025-05-25 10:48:05', '2025-05-25 10:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('active','ended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`, `status`) VALUES
(1, 1, '2025-05-01 00:00:00', '2025-05-01 00:00:00', 'Delhi', 'active'),
(2, 2, '2025-05-02 00:00:00', '2025-05-02 00:00:00', 'Mumbai', ''),
(3, 3, '2025-05-03 00:00:00', '2025-05-03 00:00:00', 'Bangalore', ''),
(4, 4, '2025-05-04 00:00:00', '2025-05-04 00:00:00', 'Ahmedabad', ''),
(5, 5, '2025-05-05 00:00:00', '2025-05-05 00:00:00', 'Pune', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_social_accounts`
--

CREATE TABLE `user_social_accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `provider_id` varchar(255) NOT NULL,
  `token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voice_assistant_config`
--

CREATE TABLE `voice_assistant_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voice_assistant_config`
--

INSERT INTO `voice_assistant_config` (`id`, `provider`, `api_key`, `created_at`) VALUES
(1, 'Value for provider 1', 'Value for api_key 1', '2025-05-01 00:00:00'),
(2, 'Value for provider 2', 'Value for api_key 2', '2025-05-07 00:00:00'),
(3, 'Value for provider 3', 'Value for api_key 3', '2025-05-13 00:00:00'),
(4, 'Value for provider 4', 'Value for api_key 4', '2025-05-19 00:00:00'),
(5, 'Value for provider 5', 'Value for api_key 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_automation_config`
--

CREATE TABLE `whatsapp_automation_config` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `sender_number` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `whatsapp_automation_config`
--

INSERT INTO `whatsapp_automation_config` (`id`, `provider`, `api_key`, `sender_number`, `created_at`) VALUES
(1, 'Value for provider 1', 'Value for api_key 1', 'Value for sender_number 1', '2025-05-01 00:00:00'),
(2, 'Value for provider 2', 'Value for api_key 2', 'Value for sender_number 2', '2025-05-07 00:00:00'),
(3, 'Value for provider 3', 'Value for api_key 3', 'Value for sender_number 3', '2025-05-13 00:00:00'),
(4, 'Value for provider 4', 'Value for api_key 4', 'Value for sender_number 4', '2025-05-19 00:00:00'),
(5, 'Value for provider 5', 'Value for api_key 5', 'Value for sender_number 5', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `workflows`
--

CREATE TABLE `workflows` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `definition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`definition`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflow_automations`
--

CREATE TABLE `workflow_automations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workflow_automations`
--

INSERT INTO `workflow_automations` (`id`, `name`, `provider`, `webhook_url`, `status`, `created_at`) VALUES
(1, 'Rahul Sharma', 'Value for provider 1', 'Value for webhook_url 1', 'active', '2025-05-01 00:00:00'),
(2, 'Priya Singh', 'Value for provider 2', 'Value for webhook_url 2', 'pending', '2025-05-07 00:00:00'),
(3, 'Amit Kumar', 'Value for provider 3', 'Value for webhook_url 3', 'completed', '2025-05-13 00:00:00'),
(4, 'Neha Patel', 'Value for provider 4', 'Value for webhook_url 4', 'cancelled', '2025-05-19 00:00:00'),
(5, 'Vikram Mehta', 'Value for provider 5', 'Value for webhook_url 5', 'active', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Structure for view `booking_summary`
--
DROP TABLE IF EXISTS `booking_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `booking_summary`  AS SELECT `b`.`id` AS `booking_id`, `b`.`booking_number` AS `booking_number`, `b`.`booking_date` AS `booking_date`, `c`.`id` AS `customer_id`, `u`.`name` AS `customer_name`, `u`.`phone` AS `customer_phone`, `p`.`id` AS `property_id`, `p`.`title` AS `property_title`, concat_ws(', ',`p`.`address`,`p`.`city`,`p`.`state`,`p`.`country`,`p`.`postal_code`) AS `property_address`, `p`.`price` AS `property_price`, `b`.`amount` AS `booking_amount`, `b`.`status` AS `booking_status`, `a`.`id` AS `associate_id`, `a`.`name` AS `associate_name`, `b`.`created_at` AS `created_at` FROM ((((`bookings` `b` left join `customers` `c` on(`b`.`customer_id` = `c`.`id`)) left join `users` `u` on(`c`.`user_id` = `u`.`id`)) left join `properties` `p` on(`b`.`property_id` = `p`.`id`)) left join `associates` `a` on(`b`.`associate_id` = `a`.`id`)) ORDER BY `b`.`booking_date` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `customer_summary`
--
DROP TABLE IF EXISTS `customer_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_summary`  AS SELECT `c`.`id` AS `customer_id`, `u`.`name` AS `customer_name`, `u`.`email` AS `email`, `u`.`phone` AS `mobile`, `c`.`customer_type` AS `customer_type`, `c`.`kyc_status` AS `kyc_status`, count(distinct `b`.`id`) AS `total_bookings`, coalesce(sum(`b`.`amount`),0) AS `total_investment`, max(`b`.`booking_date`) AS `last_booking_date`, to_days(curdate()) - to_days(max(ifnull(`b`.`booking_date`,curdate()))) AS `days_since_last_booking`, `c`.`created_at` AS `customer_since` FROM ((`customers` `c` left join `users` `u` on(`c`.`user_id` = `u`.`id`)) left join `bookings` `b` on(`c`.`id` = `b`.`customer_id`)) GROUP BY `c`.`id`, `u`.`name`, `u`.`email`, `u`.`phone`, `c`.`customer_type`, `c`.`kyc_status`, `c`.`created_at` ;

-- --------------------------------------------------------

--
-- Structure for view `mlm_performance`
--
DROP TABLE IF EXISTS `mlm_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `mlm_performance`  AS SELECT `a`.`id` AS `associate_id`, `a`.`name` AS `associate_name`, `a`.`commission_rate` AS `commission_rate`, `a`.`status` AS `status`, count(distinct `c`.`id`) AS `total_referrals`, count(distinct `b`.`id`) AS `total_sales`, coalesce(sum(`b`.`amount`),0) AS `total_sales_amount`, coalesce(sum(`b`.`amount`),0) * (`a`.`commission_rate` / 100) AS `estimated_commission` FROM ((`associates` `a` left join `customers` `c` on(`a`.`id` = `c`.`referred_by`)) left join `bookings` `b` on(`a`.`id` = `b`.`associate_id`)) GROUP BY `a`.`id`, `a`.`name`, `a`.`commission_rate`, `a`.`status` ;

-- --------------------------------------------------------

--
-- Structure for view `payment_summary`
--
DROP TABLE IF EXISTS `payment_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_summary`  AS SELECT `p`.`id` AS `payment_id`, `p`.`booking_id` AS `booking_id`, `b`.`booking_number` AS `booking_number`, `c`.`id` AS `customer_id`, `u`.`name` AS `customer_name`, `p`.`amount` AS `payment_amount`, `p`.`payment_date` AS `payment_date`, `p`.`method` AS `payment_method`, `p`.`status` AS `payment_status`, `b`.`amount` AS `booking_amount`, (select coalesce(sum(`payments`.`amount`),0) from `payments` where `payments`.`booking_id` = `b`.`id` and `payments`.`status` = 'completed') AS `total_paid_amount`, `b`.`amount`- (select coalesce(sum(`payments`.`amount`),0) from `payments` where `payments`.`booking_id` = `b`.`id` and `payments`.`status` = 'completed') AS `pending_amount` FROM (((`payments` `p` left join `bookings` `b` on(`p`.`booking_id` = `b`.`id`)) left join `customers` `c` on(`b`.`customer_id` = `c`.`id`)) left join `users` `u` on(`c`.`user_id` = `u`.`id`)) ORDER BY `p`.`payment_date` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `accounting_payments`
--
ALTER TABLE `accounting_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `fk_payment_bank` (`bank_account_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payment_type` (`payment_type`),
  ADD KEY `idx_party_type_id` (`party_type`,`party_id`);

--
-- Indexes for table `accounting_settings`
--
ALTER TABLE `accounting_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auser` (`auser`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `ai_chatbot_config`
--
ALTER TABLE `ai_chatbot_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_config`
--
ALTER TABLE `ai_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `ai_lead_scores`
--
ALTER TABLE `ai_lead_scores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_developers`
--
ALTER TABLE `api_developers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_integrations`
--
ALTER TABLE `api_integrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_rate_limits`
--
ALTER TABLE `api_rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_key` (`api_key`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `api_key_timestamp` (`api_key`,`timestamp`);

--
-- Indexes for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_key_id` (`api_key_id`),
  ADD KEY `request_time` (`request_time`);

--
-- Indexes for table `api_sandbox`
--
ALTER TABLE `api_sandbox`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_usage`
--
ALTER TABLE `api_usage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_store`
--
ALTER TABLE `app_store`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ar_vr_tours`
--
ALTER TABLE `ar_vr_tours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `associates`
--
ALTER TABLE `associates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_associate_mobile` (`mobile`),
  ADD KEY `idx_associate_sponsor` (`sponsor_id`),
  ADD KEY `idx_associate_level` (`level_id`),
  ADD KEY `idx_associate_status` (`status`);

--
-- Indexes for table `associate_levels`
--
ALTER TABLE `associate_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_name` (`name`),
  ADD KEY `idx_level_business` (`min_business`,`max_business`);

--
-- Indexes for table `associate_mlm`
--
ALTER TABLE `associate_mlm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `audit_access_log`
--
ALTER TABLE `audit_access_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_table` (`table_name`),
  ADD KEY `idx_audit_record` (`record_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_date` (`created_at`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `idx_bank_status` (`status`);

--
-- Indexes for table `bank_reconciliation`
--
ALTER TABLE `bank_reconciliation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reconciliation_bank` (`bank_account_id`),
  ADD KEY `idx_reconciliation_date` (`reconciliation_date`);

--
-- Indexes for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bank_transaction` (`bank_account_id`),
  ADD KEY `fk_bank_payment` (`payment_id`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_reconciled` (`is_reconciled`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plot_id` (`plot_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `idx_booking_status` (`status`),
  ADD KEY `idx_booking_date` (`booking_date`);

--
-- Indexes for table `budget_planning`
--
ALTER TABLE `budget_planning`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_budget_account` (`account_id`),
  ADD KEY `idx_budget_year` (`budget_year`),
  ADD KEY `idx_budget_period` (`period_start`,`period_end`);

--
-- Indexes for table `builders`
--
ALTER TABLE `builders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `builder_email` (`email`),
  ADD KEY `idx_builder_mobile` (`mobile`),
  ADD KEY `idx_builder_status` (`status`);

--
-- Indexes for table `builder_payments`
--
ALTER TABLE `builder_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_project` (`project_id`),
  ADD KEY `fk_payment_builder` (`builder_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `cash_flow_projections`
--
ALTER TABLE `cash_flow_projections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_projection_date` (`projection_date`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_code` (`account_code`),
  ADD KEY `parent_account_id` (`parent_account_id`),
  ADD KEY `idx_account_type` (`account_type`),
  ADD KEY `idx_account_code` (`account_code`);

--
-- Indexes for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `colonies`
--
ALTER TABLE `colonies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `colonies` ADD FULLTEXT KEY `ft_name_location` (`name`,`location`);

--
-- Indexes for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`);

--
-- Indexes for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_employees`
--
ALTER TABLE `company_employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `company_projects`
--
ALTER TABLE `company_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_property_levels`
--
ALTER TABLE `company_property_levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_plan_level` (`plan_id`,`level_order`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `construction_projects`
--
ALTER TABLE `construction_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_builder` (`builder_id`),
  ADD KEY `fk_project_site` (`site_id`),
  ADD KEY `idx_project_status` (`status`),
  ADD KEY `idx_project_dates` (`start_date`,`estimated_completion`);

--
-- Indexes for table `crm_leads`
--
ALTER TABLE `crm_leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_customer_email` (`email`),
  ADD KEY `idx_customer_mobile` (`mobile`),
  ADD KEY `idx_customer_kyc` (`kyc_status`),
  ADD KEY `idx_customer_referrer` (`referred_by`);

--
-- Indexes for table `customers_ledger`
--
ALTER TABLE `customers_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_mobile` (`mobile`),
  ADD KEY `idx_customer_status` (`status`),
  ADD KEY `idx_customer_balance` (`current_balance`);

--
-- Indexes for table `customer_documents`
--
ALTER TABLE `customer_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_inquiries`
--
ALTER TABLE `customer_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inquiry_customer` (`customer_id`),
  ADD KEY `idx_inquiry_status` (`status`),
  ADD KEY `idx_inquiry_type` (`inquiry_type`);

--
-- Indexes for table `customer_journeys`
--
ALTER TABLE `customer_journeys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_stream_events`
--
ALTER TABLE `data_stream_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `emi`
--
ALTER TABLE `emi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `emi_installments`
--
ALTER TABLE `emi_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_emi_plan` (`emi_plan_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `emi_plans`
--
ALTER TABLE `emi_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property` (`property_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `emi_schedule`
--
ALTER TABLE `emi_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_emi_customer` (`customer_id`),
  ADD KEY `fk_emi_booking` (`booking_id`),
  ADD KEY `fk_emi_payment` (`payment_id`),
  ADD KEY `idx_emi_due_date` (`due_date`),
  ADD KEY `idx_emi_status` (`status`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `farmer_land_holdings`
--
ALTER TABLE `farmer_land_holdings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farmer_number` (`farmer_number`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback_tickets`
--
ALTER TABLE `feedback_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_report_period` (`report_period`),
  ADD KEY `idx_report_dates` (`from_date`,`to_date`);

--
-- Indexes for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_name` (`year_name`),
  ADD KEY `idx_fy_dates` (`start_date`,`end_date`),
  ADD KEY `idx_fy_current` (`is_current`);

--
-- Indexes for table `foreclosure_logs`
--
ALTER TABLE `foreclosure_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempted_by` (`attempted_by`),
  ADD KEY `idx_emi_plan` (`emi_plan_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_attempted_at` (`attempted_at`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `global_payments`
--
ALTER TABLE `global_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gst_records`
--
ALTER TABLE `gst_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gst_date` (`transaction_date`),
  ADD KEY `idx_gst_type` (`transaction_type`),
  ADD KEY `idx_gst_period` (`gst_return_period`);

--
-- Indexes for table `hybrid_commission_plans`
--
ALTER TABLE `hybrid_commission_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_code` (`plan_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_plan_type` (`plan_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `hybrid_commission_records`
--
ALTER TABLE `hybrid_commission_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_associate_type` (`associate_id`,`commission_type`),
  ADD KEY `idx_payout_status` (`payout_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `income_records`
--
ALTER TABLE `income_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `income_number` (`income_number`),
  ADD KEY `fk_income_bank` (`bank_account_id`),
  ADD KEY `fk_income_customer` (`customer_id`),
  ADD KEY `idx_income_date` (`income_date`),
  ADD KEY `idx_income_category` (`income_category`),
  ADD KEY `idx_income_status` (`status`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iot_devices`
--
ALTER TABLE `iot_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iot_device_events`
--
ALTER TABLE `iot_device_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `journal_number` (`journal_number`),
  ADD KEY `idx_journal_date` (`entry_date`),
  ADD KEY `idx_journal_status` (`status`),
  ADD KEY `idx_journal_type` (`entry_type`);

--
-- Indexes for table `journal_entry_details`
--
ALTER TABLE `journal_entry_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_journal_entry` (`journal_entry_id`),
  ADD KEY `fk_journal_account` (`account_id`);

--
-- Indexes for table `jwt_blacklist`
--
ALTER TABLE `jwt_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(255)),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `land_purchases`
--
ALTER TABLE `land_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `lead_files`
--
ALTER TABLE `lead_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lead_notes`
--
ALTER TABLE `lead_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legal_services`
--
ALTER TABLE `legal_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_number` (`loan_number`),
  ADD KEY `fk_loan_bank` (`bank_account_id`),
  ADD KEY `idx_loan_status` (`status`),
  ADD KEY `idx_loan_dates` (`start_date`,`end_date`);

--
-- Indexes for table `loan_emi_schedule`
--
ALTER TABLE `loan_emi_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_emi_loan` (`loan_id`),
  ADD KEY `fk_emi_payment` (`payment_id`),
  ADD KEY `idx_emi_due_date` (`due_date`),
  ADD KEY `idx_emi_status` (`status`);

--
-- Indexes for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing_strategies`
--
ALTER TABLE `marketing_strategies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketplace_apps`
--
ALTER TABLE `marketplace_apps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_migration` (`version`,`migration_name`);

--
-- Indexes for table `mlm_agents`
--
ALTER TABLE `mlm_agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mobile` (`mobile`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_referral_code` (`referral_code`),
  ADD KEY `sponsor_id` (`sponsor_id`);

--
-- Indexes for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `payout_id` (`payout_id`);

--
-- Indexes for table `mlm_commission_analytics`
--
ALTER TABLE `mlm_commission_analytics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_associate_period` (`associate_id`,`period_date`),
  ADD KEY `idx_associate_period` (`associate_id`,`period_date`);

--
-- Indexes for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commission_id` (`commission_id`);

--
-- Indexes for table `mlm_commission_records`
--
ALTER TABLE `mlm_commission_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_associate_status` (`associate_id`,`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_commission_records_associate` (`associate_id`,`status`);

--
-- Indexes for table `mlm_commission_targets`
--
ALTER TABLE `mlm_commission_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_associate_period` (`associate_id`,`target_period`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `mlm_payouts`
--
ALTER TABLE `mlm_payouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_associate_month` (`associate_id`,`payout_month`),
  ADD KEY `idx_payout_month` (`payout_month`),
  ADD KEY `idx_payout_status` (`status`);

--
-- Indexes for table `mlm_rank_advancements`
--
ALTER TABLE `mlm_rank_advancements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_associate_date` (`associate_id`,`advancement_date`);

--
-- Indexes for table `mlm_tree`
--
ALTER TABLE `mlm_tree`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `mlm_withdrawal_requests`
--
ALTER TABLE `mlm_withdrawal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_associate_status` (`associate_id`,`status`);

--
-- Indexes for table `mobile_devices`
--
ALTER TABLE `mobile_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`type`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type` (`type`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partner_certification`
--
ALTER TABLE `partner_certification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partner_rewards`
--
ALTER TABLE `partner_rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_gateway_config`
--
ALTER TABLE `payment_gateway_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_orders`
--
ALTER TABLE `payment_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `razorpay_order_id` (`razorpay_order_id`),
  ADD KEY `idx_payment_orders_razorpay_id` (`razorpay_order_id`),
  ADD KEY `idx_payment_orders_status` (`status`),
  ADD KEY `idx_payment_orders_created_at` (`created_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plots`
--
ALTER TABLE `plots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colonies_id` (`colonies_id`),
  ADD KEY `idx_plot_status` (`status`);

--
-- Indexes for table `plot_development`
--
ALTER TABLE `plot_development`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_plot_number` (`plot_number`),
  ADD KEY `fk_plot_land_purchase` (`land_purchase_id`),
  ADD KEY `fk_plot_customer` (`customer_id`),
  ADD KEY `idx_plot_status` (`status`);

--
-- Indexes for table `plot_rate_calculations`
--
ALTER TABLE `plot_rate_calculations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `calculated_by` (`calculated_by`),
  ADD KEY `idx_calculation_date` (`calculation_date`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_amenities`
--
ALTER TABLE `project_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `project_category_relations`
--
ALTER TABLE `project_category_relations`
  ADD PRIMARY KEY (`project_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_progress`
--
ALTER TABLE `project_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_progress_project` (`project_id`),
  ADD KEY `idx_progress_date` (`created_at`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_type_id` (`property_type_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`),
  ADD KEY `hot_offer` (`hot_offer`);
ALTER TABLE `properties` ADD FULLTEXT KEY `title` (`title`,`description`,`address`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`),
  ADD KEY `type` (`type`),
  ADD KEY `city` (`city`),
  ADD KEY `state` (`state`);

--
-- Indexes for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `amenity_type` (`amenity_type`);

--
-- Indexes for table `property_bookings`
--
ALTER TABLE `property_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_order_id` (`payment_order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_property_bookings_user_id` (`user_id`),
  ADD KEY `idx_property_bookings_property_id` (`property_id`),
  ADD KEY `idx_property_bookings_status` (`status`);

--
-- Indexes for table `property_development_costs`
--
ALTER TABLE `property_development_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property_cost_type` (`property_id`,`cost_type`);

--
-- Indexes for table `property_favorites`
--
ALTER TABLE `property_favorites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_features`
--
ALTER TABLE `property_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_feature_mappings`
--
ALTER TABLE `property_feature_mappings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_feature` (`property_id`,`feature_id`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`,`is_primary`,`sort_order`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_visits`
--
ALTER TABLE `property_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `visit_date` (`visit_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchase_supplier` (`supplier_id`),
  ADD KEY `idx_purchase_date` (`invoice_date`),
  ADD KEY `idx_purchase_status` (`status`);

--
-- Indexes for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchase_item` (`invoice_id`);

--
-- Indexes for table `real_estate_properties`
--
ALTER TABLE `real_estate_properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_code` (`property_code`),
  ADD KEY `idx_property_type` (`property_type`),
  ADD KEY `idx_category` (`property_category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`location`);

--
-- Indexes for table `recurring_transactions`
--
ALTER TABLE `recurring_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recurring_account` (`account_id`),
  ADD KEY `idx_recurring_due_date` (`next_due_date`),
  ADD KEY `idx_recurring_status` (`status`);

--
-- Indexes for table `rental_properties`
--
ALTER TABLE `rental_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_property_id` (`rental_property_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resale_commissions`
--
ALTER TABLE `resale_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associate_id` (`associate_id`),
  ADD KEY `resale_property_id` (`resale_property_id`);

--
-- Indexes for table `resale_properties`
--
ALTER TABLE `resale_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `resell_commission_structure`
--
ALTER TABLE `resell_commission_structure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_plan_category` (`plan_id`,`property_category`);

--
-- Indexes for table `reward_history`
--
ALTER TABLE `reward_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_change_approvals`
--
ALTER TABLE `role_change_approvals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saas_instances`
--
ALTER TABLE `saas_instances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `salary_plan`
--
ALTER TABLE `salary_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `fk_invoice_customer` (`customer_id`),
  ADD KEY `idx_invoice_date` (`invoice_date`),
  ADD KEY `idx_invoice_status` (`status`);

--
-- Indexes for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_item` (`invoice_id`);

--
-- Indexes for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `seo_metadata`
--
ALTER TABLE `seo_metadata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_name` (`page_name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_site_status` (`status`),
  ADD KEY `idx_site_type` (`site_type`),
  ADD KEY `idx_site_location` (`city`,`state`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `smart_contracts`
--
ALTER TABLE `smart_contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_media_links`
--
ALTER TABLE `social_media_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `platform_name` (`platform_name`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_mobile` (`mobile`),
  ADD KEY `idx_supplier_status` (`status`),
  ADD KEY `idx_supplier_balance` (`current_balance`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `table_name`
--
ALTER TABLE `table_name`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `third_party_integrations`
--
ALTER TABLE `third_party_integrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `upload_audit_log`
--
ALTER TABLE `upload_audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_profile_picture` (`profile_picture`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_preference` (`user_id`,`preference_key`),
  ADD KEY `idx_user_preferences_key` (`preference_key`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_provider` (`provider`,`provider_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `voice_assistant_config`
--
ALTER TABLE `voice_assistant_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `whatsapp_automation_config`
--
ALTER TABLE `whatsapp_automation_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workflows`
--
ALTER TABLE `workflows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workflow_automations`
--
ALTER TABLE `workflow_automations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `accounting_payments`
--
ALTER TABLE `accounting_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accounting_settings`
--
ALTER TABLE `accounting_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_chatbot_config`
--
ALTER TABLE `ai_chatbot_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_config`
--
ALTER TABLE `ai_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_lead_scores`
--
ALTER TABLE `ai_lead_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ai_logs`
--
ALTER TABLE `ai_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_developers`
--
ALTER TABLE `api_developers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_integrations`
--
ALTER TABLE `api_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `api_rate_limits`
--
ALTER TABLE `api_rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `api_sandbox`
--
ALTER TABLE `api_sandbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `api_usage`
--
ALTER TABLE `api_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `app_store`
--
ALTER TABLE `app_store`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ar_vr_tours`
--
ALTER TABLE `ar_vr_tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `associates`
--
ALTER TABLE `associates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `associate_levels`
--
ALTER TABLE `associate_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `associate_mlm`
--
ALTER TABLE `associate_mlm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_access_log`
--
ALTER TABLE `audit_access_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_reconciliation`
--
ALTER TABLE `bank_reconciliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `budget_planning`
--
ALTER TABLE `budget_planning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `builders`
--
ALTER TABLE `builders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `builder_payments`
--
ALTER TABLE `builder_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_flow_projections`
--
ALTER TABLE `cash_flow_projections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `colonies`
--
ALTER TABLE `colonies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company_employees`
--
ALTER TABLE `company_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `company_projects`
--
ALTER TABLE `company_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_property_levels`
--
ALTER TABLE `company_property_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `construction_projects`
--
ALTER TABLE `construction_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_leads`
--
ALTER TABLE `crm_leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customers_ledger`
--
ALTER TABLE `customers_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_documents`
--
ALTER TABLE `customer_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_inquiries`
--
ALTER TABLE `customer_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_journeys`
--
ALTER TABLE `customer_journeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_stream_events`
--
ALTER TABLE `data_stream_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `emi`
--
ALTER TABLE `emi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `emi_installments`
--
ALTER TABLE `emi_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emi_plans`
--
ALTER TABLE `emi_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emi_schedule`
--
ALTER TABLE `emi_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `farmer_land_holdings`
--
ALTER TABLE `farmer_land_holdings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `feedback_tickets`
--
ALTER TABLE `feedback_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `financial_reports`
--
ALTER TABLE `financial_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_years`
--
ALTER TABLE `financial_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `foreclosure_logs`
--
ALTER TABLE `foreclosure_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `global_payments`
--
ALTER TABLE `global_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gst_records`
--
ALTER TABLE `gst_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hybrid_commission_plans`
--
ALTER TABLE `hybrid_commission_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hybrid_commission_records`
--
ALTER TABLE `hybrid_commission_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `income_records`
--
ALTER TABLE `income_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `iot_devices`
--
ALTER TABLE `iot_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `iot_device_events`
--
ALTER TABLE `iot_device_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `journal_entry_details`
--
ALTER TABLE `journal_entry_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jwt_blacklist`
--
ALTER TABLE `jwt_blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `land_purchases`
--
ALTER TABLE `land_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `lead_files`
--
ALTER TABLE `lead_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_notes`
--
ALTER TABLE `lead_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `legal_documents`
--
ALTER TABLE `legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `legal_services`
--
ALTER TABLE `legal_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_emi_schedule`
--
ALTER TABLE `loan_emi_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marketing_strategies`
--
ALTER TABLE `marketing_strategies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `marketplace_apps`
--
ALTER TABLE `marketplace_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mlm_agents`
--
ALTER TABLE `mlm_agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_commission_analytics`
--
ALTER TABLE `mlm_commission_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_commission_records`
--
ALTER TABLE `mlm_commission_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_commission_targets`
--
ALTER TABLE `mlm_commission_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mlm_payouts`
--
ALTER TABLE `mlm_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_rank_advancements`
--
ALTER TABLE `mlm_rank_advancements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mlm_tree`
--
ALTER TABLE `mlm_tree`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mlm_withdrawal_requests`
--
ALTER TABLE `mlm_withdrawal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mobile_devices`
--
ALTER TABLE `mobile_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `partner_certification`
--
ALTER TABLE `partner_certification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `partner_rewards`
--
ALTER TABLE `partner_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payment_gateway_config`
--
ALTER TABLE `payment_gateway_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_orders`
--
ALTER TABLE `payment_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `plots`
--
ALTER TABLE `plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `plot_development`
--
ALTER TABLE `plot_development`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plot_rate_calculations`
--
ALTER TABLE `plot_rate_calculations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `project_amenities`
--
ALTER TABLE `project_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_gallery`
--
ALTER TABLE `project_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_progress`
--
ALTER TABLE `project_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_amenities`
--
ALTER TABLE `property_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `property_bookings`
--
ALTER TABLE `property_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_development_costs`
--
ALTER TABLE `property_development_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_favorites`
--
ALTER TABLE `property_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_features`
--
ALTER TABLE `property_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `property_feature_mappings`
--
ALTER TABLE `property_feature_mappings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `property_visits`
--
ALTER TABLE `property_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `real_estate_properties`
--
ALTER TABLE `real_estate_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `recurring_transactions`
--
ALTER TABLE `recurring_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental_properties`
--
ALTER TABLE `rental_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rent_payments`
--
ALTER TABLE `rent_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resale_commissions`
--
ALTER TABLE `resale_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resale_properties`
--
ALTER TABLE `resale_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `resell_commission_structure`
--
ALTER TABLE `resell_commission_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reward_history`
--
ALTER TABLE `reward_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `role_change_approvals`
--
ALTER TABLE `role_change_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `saas_instances`
--
ALTER TABLE `saas_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `salary_plan`
--
ALTER TABLE `salary_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_searches`
--
ALTER TABLE `saved_searches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seo_metadata`
--
ALTER TABLE `seo_metadata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `smart_contracts`
--
ALTER TABLE `smart_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `social_media_links`
--
ALTER TABLE `social_media_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `table_name`
--
ALTER TABLE `table_name`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `third_party_integrations`
--
ALTER TABLE `third_party_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `upload_audit_log`
--
ALTER TABLE `upload_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_social_accounts`
--
ALTER TABLE `user_social_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voice_assistant_config`
--
ALTER TABLE `voice_assistant_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `whatsapp_automation_config`
--
ALTER TABLE `whatsapp_automation_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workflows`
--
ALTER TABLE `workflows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workflow_automations`
--
ALTER TABLE `workflow_automations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounting_payments`
--
ALTER TABLE `accounting_payments`
  ADD CONSTRAINT `fk_payment_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  ADD CONSTRAINT `ai_chatbot_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_config`
--
ALTER TABLE `ai_config`
  ADD CONSTRAINT `ai_config_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD CONSTRAINT `ai_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD CONSTRAINT `api_request_logs_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `associates`
--
ALTER TABLE `associates`
  ADD CONSTRAINT `fk_associate_level` FOREIGN KEY (`level_id`) REFERENCES `associate_levels` (`id`),
  ADD CONSTRAINT `fk_associate_sponsor` FOREIGN KEY (`sponsor_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bank_reconciliation`
--
ALTER TABLE `bank_reconciliation`
  ADD CONSTRAINT `fk_reconciliation_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `fk_bank_payment` FOREIGN KEY (`payment_id`) REFERENCES `accounting_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bank_transaction` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budget_planning`
--
ALTER TABLE `budget_planning`
  ADD CONSTRAINT `fk_budget_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `builder_payments`
--
ALTER TABLE `builder_payments`
  ADD CONSTRAINT `fk_payment_builder` FOREIGN KEY (`builder_id`) REFERENCES `builders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_project` FOREIGN KEY (`project_id`) REFERENCES `construction_projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD CONSTRAINT `fk_parent_account` FOREIGN KEY (`parent_account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `commission_payouts`
--
ALTER TABLE `commission_payouts`
  ADD CONSTRAINT `commission_payouts_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_employees`
--
ALTER TABLE `company_employees`
  ADD CONSTRAINT `company_employees_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `company_employees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `company_property_levels`
--
ALTER TABLE `company_property_levels`
  ADD CONSTRAINT `company_property_levels_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `hybrid_commission_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `construction_projects`
--
ALTER TABLE `construction_projects`
  ADD CONSTRAINT `fk_project_builder` FOREIGN KEY (`builder_id`) REFERENCES `builders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_customer_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `associates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `emi`
--
ALTER TABLE `emi`
  ADD CONSTRAINT `emi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `emi_schedule`
--
ALTER TABLE `emi_schedule`
  ADD CONSTRAINT `fk_emi_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_emi_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_emi_payment_ref` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `farmer_land_holdings`
--
ALTER TABLE `farmer_land_holdings`
  ADD CONSTRAINT `farmer_land_holdings_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD CONSTRAINT `farmer_profiles_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `farmer_profiles_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `foreclosure_logs`
--
ALTER TABLE `foreclosure_logs`
  ADD CONSTRAINT `foreclosure_logs_ibfk_1` FOREIGN KEY (`emi_plan_id`) REFERENCES `emi_plans` (`id`),
  ADD CONSTRAINT `foreclosure_logs_ibfk_2` FOREIGN KEY (`attempted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `hybrid_commission_plans`
--
ALTER TABLE `hybrid_commission_plans`
  ADD CONSTRAINT `hybrid_commission_plans_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hybrid_commission_records`
--
ALTER TABLE `hybrid_commission_records`
  ADD CONSTRAINT `hybrid_commission_records_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hybrid_commission_records_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `real_estate_properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hybrid_commission_records_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `income_records`
--
ALTER TABLE `income_records`
  ADD CONSTRAINT `fk_income_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_income_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers_ledger` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `journal_entry_details`
--
ALTER TABLE `journal_entry_details`
  ADD CONSTRAINT `fk_journal_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`),
  ADD CONSTRAINT `fk_journal_entry` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `land_purchases`
--
ALTER TABLE `land_purchases`
  ADD CONSTRAINT `land_purchases_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`);

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loan_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loan_emi_schedule`
--
ALTER TABLE `loan_emi_schedule`
  ADD CONSTRAINT `fk_emi_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_emi_payment` FOREIGN KEY (`payment_id`) REFERENCES `accounting_payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mlm_commissions`
--
ALTER TABLE `mlm_commissions`
  ADD CONSTRAINT `fk_mlm_commission_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mlm_commissions_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mlm_commissions_ibfk_2` FOREIGN KEY (`payout_id`) REFERENCES `commission_payouts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mlm_commission_analytics`
--
ALTER TABLE `mlm_commission_analytics`
  ADD CONSTRAINT `mlm_commission_analytics_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_commission_ledger`
--
ALTER TABLE `mlm_commission_ledger`
  ADD CONSTRAINT `mlm_commission_ledger_ibfk_1` FOREIGN KEY (`commission_id`) REFERENCES `mlm_commissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_commission_records`
--
ALTER TABLE `mlm_commission_records`
  ADD CONSTRAINT `mlm_commission_records_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mlm_commission_records_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_commission_targets`
--
ALTER TABLE `mlm_commission_targets`
  ADD CONSTRAINT `mlm_commission_targets_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_payouts`
--
ALTER TABLE `mlm_payouts`
  ADD CONSTRAINT `fk_mlm_payout_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_rank_advancements`
--
ALTER TABLE `mlm_rank_advancements`
  ADD CONSTRAINT `mlm_rank_advancements_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mlm_tree`
--
ALTER TABLE `mlm_tree`
  ADD CONSTRAINT `mlm_tree_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mlm_tree_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `mlm_withdrawal_requests`
--
ALTER TABLE `mlm_withdrawal_requests`
  ADD CONSTRAINT `mlm_withdrawal_requests_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `plots`
--
ALTER TABLE `plots`
  ADD CONSTRAINT `plots_ibfk_1` FOREIGN KEY (`colonies_id`) REFERENCES `colonies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plot_development`
--
ALTER TABLE `plot_development`
  ADD CONSTRAINT `fk_plot_land_purchase` FOREIGN KEY (`land_purchase_id`) REFERENCES `land_purchases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plot_rate_calculations`
--
ALTER TABLE `plot_rate_calculations`
  ADD CONSTRAINT `plot_rate_calculations_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `real_estate_properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plot_rate_calculations_ibfk_2` FOREIGN KEY (`calculated_by`) REFERENCES `mlm_agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_amenities`
--
ALTER TABLE `project_amenities`
  ADD CONSTRAINT `project_amenities_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD CONSTRAINT `project_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_category_relations`
--
ALTER TABLE `project_category_relations`
  ADD CONSTRAINT `project_category_relations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_category_relations_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_gallery`
--
ALTER TABLE `project_gallery`
  ADD CONSTRAINT `project_gallery_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_progress`
--
ALTER TABLE `project_progress`
  ADD CONSTRAINT `fk_progress_project` FOREIGN KEY (`project_id`) REFERENCES `construction_projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_bookings`
--
ALTER TABLE `property_bookings`
  ADD CONSTRAINT `property_bookings_ibfk_1` FOREIGN KEY (`payment_order_id`) REFERENCES `payment_orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_development_costs`
--
ALTER TABLE `property_development_costs`
  ADD CONSTRAINT `property_development_costs_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `real_estate_properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_feature_mappings`
--
ALTER TABLE `property_feature_mappings`
  ADD CONSTRAINT `property_feature_mappings_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_feature_mappings_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `property_features` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD CONSTRAINT `fk_purchase_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD CONSTRAINT `fk_purchase_item` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recurring_transactions`
--
ALTER TABLE `recurring_transactions`
  ADD CONSTRAINT `fk_recurring_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_properties`
--
ALTER TABLE `rental_properties`
  ADD CONSTRAINT `rental_properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD CONSTRAINT `rent_payments_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resell_commission_structure`
--
ALTER TABLE `resell_commission_structure`
  ADD CONSTRAINT `resell_commission_structure_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `hybrid_commission_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD CONSTRAINT `fk_invoice_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers_ledger` (`id`);

--
-- Constraints for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD CONSTRAINT `fk_invoice_item` FOREIGN KEY (`invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
