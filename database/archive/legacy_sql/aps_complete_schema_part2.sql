-- ==============================
-- APS DREAM HOME - COMPLETE DATABASE SCHEMA (PART 2)
-- Continuing from Part 1
-- ==============================

USE `apsdreamhome`;

-- ==============================
-- TRANSACTION & PAYMENT SYSTEM
-- ==============================

-- 11. Transactions (Financial Transactions)
CREATE TABLE `transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NOT NULL,
    `property_id` int(11) DEFAULT NULL,
    `plot_id` int(11) DEFAULT NULL,
    `booking_id` int(11) DEFAULT NULL,
    `amount` decimal(12,2) NOT NULL,
    `type` enum('booking','payment','refund','commission','emi') NOT NULL DEFAULT 'payment',
    `status` enum('pending','completed','failed','commission_processed') NOT NULL DEFAULT 'pending',
    `payment_mode` varchar(50) DEFAULT NULL,
    `transaction_ref` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `property_id` (`property_id`),
    KEY `plot_id` (`plot_id`),
    KEY `booking_id` (`booking_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Payments (Payment Records)
CREATE TABLE `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NOT NULL,
    `booking_id` int(11) DEFAULT NULL,
    `plot_id` int(11) DEFAULT NULL,
    `amount` decimal(12,2) NOT NULL,
    `payment_type` enum('booking','emi','full_payment','advance') DEFAULT 'booking',
    `payment_mode` enum('cash','cheque','bank_transfer','online','upi') DEFAULT 'cash',
    `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
    `payment_date` date DEFAULT NULL,
    `reference_no` varchar(100) DEFAULT NULL,
    `gateway_response` text DEFAULT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `booking_id` (`booking_id`),
    KEY `plot_id` (`plot_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- EMI SYSTEM
-- ==============================

-- 13. EMI Plans
CREATE TABLE `emi_plans` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `property_id` int(11) DEFAULT NULL,
    `plot_id` int(11) DEFAULT NULL,
    `customer_id` int(11) NOT NULL,
    `booking_id` int(11) DEFAULT NULL,
    `total_amount` decimal(12,2) NOT NULL,
    `down_payment` decimal(12,2) NOT NULL,
    `emi_amount` decimal(12,2) NOT NULL,
    `interest_rate` decimal(5,2) NOT NULL,
    `tenure_months` int(11) NOT NULL,
    `start_date` date NOT NULL,
    `end_date` date NOT NULL,
    `status` enum('active','completed','defaulted','cancelled','foreclosed') NOT NULL DEFAULT 'active',
    `foreclosure_date` date DEFAULT NULL,
    `foreclosure_amount` decimal(12,2) DEFAULT NULL,
    `foreclosure_payment_id` int(11) DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `property_id` (`property_id`),
    KEY `plot_id` (`plot_id`),
    KEY `customer_id` (`customer_id`),
    KEY `booking_id` (`booking_id`),
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. EMI Installments
CREATE TABLE `emi_installments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `emi_plan_id` int(11) NOT NULL,
    `installment_number` int(11) NOT NULL,
    `amount` decimal(12,2) NOT NULL,
    `principal_amount` decimal(12,2) NOT NULL,
    `interest_amount` decimal(12,2) NOT NULL,
    `due_date` date NOT NULL,
    `payment_date` date DEFAULT NULL,
    `payment_status` enum('pending','paid','overdue','waived') NOT NULL DEFAULT 'pending',
    `payment_id` int(11) DEFAULT NULL,
    `reminder_sent` tinyint(1) DEFAULT 0,
    `last_reminder_date` datetime DEFAULT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `emi_plan_id` (`emi_plan_id`),
    KEY `payment_status` (`payment_status`),
    KEY `due_date` (`due_date`),
    KEY `payment_id` (`payment_id`),
    FOREIGN KEY (`emi_plan_id`) REFERENCES `emi_plans` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- MLM COMMISSION SYSTEM
-- ==============================

-- 15. MLM Commissions
CREATE TABLE `mlm_commissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `associate_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `property_id` int(11) DEFAULT NULL,
    `plot_id` int(11) DEFAULT NULL,
    `booking_id` int(11) DEFAULT NULL,
    `transaction_id` int(11) DEFAULT NULL,
    `commission_plan_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `commission_amount` decimal(10,2) NOT NULL,
    `commission_type` enum('direct_commission','level_commission','bonus','override') DEFAULT 'direct_commission',
    `level` int(11) NOT NULL DEFAULT 1,
    `direct_percentage` decimal(5,2) DEFAULT NULL,
    `difference_percentage` decimal(5,2) DEFAULT NULL,
    `upline_id` int(11) DEFAULT NULL,
    `is_direct` tinyint(1) DEFAULT 0,
    `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
    `paid_date` date DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `associate_id` (`associate_id`),
    KEY `customer_id` (`customer_id`),
    KEY `property_id` (`property_id`),
    KEY `plot_id` (`plot_id`),
    KEY `booking_id` (`booking_id`),
    KEY `transaction_id` (`transaction_id`),
    FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Commission Transactions (Dashboard Compatible)
CREATE TABLE `commission_transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `associate_id` int(11) NOT NULL,
    `commission_amount` decimal(15,2) NOT NULL,
    `transaction_type` enum('direct','level','bonus','override') DEFAULT 'direct',
    `related_booking_id` int(11) DEFAULT NULL,
    `status` enum('pending','paid','cancelled') DEFAULT 'pending',
    `paid_date` date DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `associate_id` (`associate_id`),
    KEY `related_booking_id` (`related_booking_id`),
    FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`related_booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Commission Payouts
CREATE TABLE `commission_payouts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `associate_id` int(11) NOT NULL,
    `payout_period_start` date NOT NULL,
    `payout_period_end` date NOT NULL,
    `total_commission` decimal(15,2) DEFAULT 0,
    `total_bonus` decimal(15,2) DEFAULT 0,
    `total_override` decimal(15,2) DEFAULT 0,
    `gross_amount` decimal(15,2) NOT NULL,
    `tds_deducted` decimal(15,2) DEFAULT 0,
    `processing_fee` decimal(15,2) DEFAULT 0,
    `net_amount` decimal(15,2) NOT NULL,
    `payout_status` enum('pending','processed','paid','cancelled') DEFAULT 'pending',
    `payout_date` date DEFAULT NULL,
    `transaction_id` varchar(100) DEFAULT NULL,
    `bank_reference` varchar(100) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `associate_id` (`associate_id`),
    FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- LEADS & CRM SYSTEM
-- ==============================

-- 18. Leads
CREATE TABLE `leads` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `source` varchar(50) DEFAULT NULL,
    `status` enum('new','contacted','qualified','converted','lost') NOT NULL DEFAULT 'new',
    `assigned_to` int(11) DEFAULT NULL,
    `converted_at` datetime DEFAULT NULL,
    `converted_amount` decimal(12,2) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `assigned_to` (`assigned_to`),
    FOREIGN KEY (`assigned_to`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- EXPENSES & ACCOUNTING
-- ==============================

-- 19. Expenses (Dashboard Compatible)
CREATE TABLE `expenses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `amount` decimal(12,2) NOT NULL,
    `expense_date` date NOT NULL,
    `payment_mode` enum('cash','cheque','bank_transfer','online') DEFAULT 'cash',
    `receipt_file` varchar(255) DEFAULT NULL,
    `status` enum('pending','approved','paid','rejected') DEFAULT 'pending',
    `approved_by` int(11) DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `approved_by` (`approved_by`),
    KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- EMPLOYEE & HR SYSTEM
-- ==============================

-- 20. Employees
CREATE TABLE `employees` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `role` varchar(50) NOT NULL,
    `salary` decimal(12,2) DEFAULT NULL,
    `join_date` date DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `password` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- FARMER & LAND SYSTEM
-- ==============================

-- 21. Farmers
CREATE TABLE `farmers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `land_area` decimal(10,2) DEFAULT NULL,
    `location` varchar(255) DEFAULT NULL,
    `kyc_doc` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Land Purchases
CREATE TABLE `land_purchases` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `farmer_id` int(11) DEFAULT NULL,
    `property_id` int(11) NOT NULL,
    `seller_name` varchar(100) DEFAULT NULL,
    `purchase_date` date DEFAULT NULL,
    `amount` decimal(15,2) DEFAULT NULL,
    `payment_amount` decimal(15,2) DEFAULT NULL,
    `registry_no` varchar(100) DEFAULT NULL,
    `agreement_doc` varchar(255) DEFAULT NULL,
    `site_location` varchar(255) DEFAULT NULL,
    `engineer` varchar(100) DEFAULT NULL,
    `map_doc` varchar(255) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `farmer_id` (`farmer_id`),
    KEY `property_id` (`property_id`),
    FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Continue in next part...
COMMIT;