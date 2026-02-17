-- CRM Tables for APS Dream Homes

-- Table for storing leads (potential customers)
CREATE TABLE IF NOT EXISTS `leads` (
  `lead_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL COMMENT 'Where the lead came from (website, referral, etc.)',
  `status` enum('new','contacted','qualified','unqualified') NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL COMMENT 'User ID of the agent/associate assigned to this lead',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`lead_id`),
  KEY `idx_lead_status` (`status`),
  KEY `idx_lead_assigned` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for storing opportunities (qualified leads with potential deals)
CREATE TABLE IF NOT EXISTS `opportunities` (
  `opportunity_id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stage` enum('prospecting','qualification','needs_analysis','proposal','negotiation','closed_won','closed_lost') NOT NULL DEFAULT 'prospecting',
  `probability` int(3) DEFAULT 0 COMMENT 'Probability of closing (0-100%)',
  `expected_close_date` date DEFAULT NULL,
  `actual_close_date` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `property_interest` int(11) DEFAULT NULL COMMENT 'Property ID the customer is interested in',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`opportunity_id`),
  KEY `fk_opportunity_lead` (`lead_id`),
  KEY `idx_opportunity_stage` (`stage`),
  KEY `idx_opportunity_assigned` (`assigned_to`),
  CONSTRAINT `fk_opportunity_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for storing activities related to leads and opportunities
CREATE TABLE IF NOT EXISTS `activities` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `type` enum('call','email','meeting','task','note') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`activity_id`),
  KEY `fk_activity_lead` (`lead_id`),
  KEY `fk_activity_opportunity` (`opportunity_id`),
  KEY `idx_activity_type` (`type`),
  KEY `idx_activity_due_date` (`due_date`),
  CONSTRAINT `fk_activity_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activity_opportunity` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`opportunity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for storing communication history
CREATE TABLE IF NOT EXISTS `communications` (
  `communication_id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `type` enum('email','call','sms','meeting','other') NOT NULL,
  `direction` enum('inbound','outbound') NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `communication_date` datetime NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'User who made/received the communication',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`communication_id`),
  KEY `fk_communication_lead` (`lead_id`),
  KEY `fk_communication_opportunity` (`opportunity_id`),
  KEY `idx_communication_date` (`communication_date`),
  CONSTRAINT `fk_communication_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_communication_opportunity` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`opportunity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for storing campaigns
CREATE TABLE IF NOT EXISTS `campaigns` (
  `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` enum('planned','active','completed','cancelled') NOT NULL DEFAULT 'planned',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `expected_revenue` decimal(12,2) DEFAULT NULL,
  `actual_cost` decimal(12,2) DEFAULT NULL,
  `actual_revenue` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`campaign_id`),
  KEY `idx_campaign_status` (`status`),
  KEY `idx_campaign_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for storing campaign members (leads associated with campaigns)
CREATE TABLE IF NOT EXISTS `campaign_members` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `status` enum('sent','opened','clicked','responded','converted','unsubscribed') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `unique_campaign_lead` (`campaign_id`,`lead_id`),
  KEY `fk_member_lead` (`lead_id`),
  KEY `idx_member_status` (`status`),
  CONSTRAINT `fk_member_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_member_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;