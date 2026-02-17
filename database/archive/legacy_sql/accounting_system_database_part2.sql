-- =========================================
-- COMPREHENSIVE ACCOUNTING SYSTEM DATABASE - PART 2
-- Advanced Financial Management Tables
-- =========================================

-- --------------------------------------------------------
-- PURCHASE INVOICE ITEMS
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `purchase_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_purchase_item` (`invoice_id`),
  CONSTRAINT `fk_purchase_item` FOREIGN KEY (`invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ACCOUNTING PAYMENTS - All Payment Transactions
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `accounting_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(50) NOT NULL UNIQUE,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_payment_bank` (`bank_account_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_payment_type` (`payment_type`),
  KEY `idx_party_type_id` (`party_type`, `party_id`),
  CONSTRAINT `fk_payment_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- EXPENSES MANAGEMENT
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_number` varchar(50) NOT NULL UNIQUE,
  `expense_date` date NOT NULL,
  `expense_category` varchar(255) NOT NULL,
  `expense_subcategory` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text NOT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','online','upi','card') NOT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `is_billable` tinyint(1) DEFAULT 0,
  `customer_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('pending','approved','paid','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_expense_bank` (`bank_account_id`),
  KEY `fk_expense_customer` (`customer_id`),
  KEY `idx_expense_date` (`expense_date`),
  KEY `idx_expense_category` (`expense_category`),
  KEY `idx_expense_status` (`status`),
  CONSTRAINT `fk_expense_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_expense_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers_ledger` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- INCOME RECORDS
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `income_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `income_number` varchar(50) NOT NULL UNIQUE,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_income_bank` (`bank_account_id`),
  KEY `fk_income_customer` (`customer_id`),
  KEY `idx_income_date` (`income_date`),
  KEY `idx_income_category` (`income_category`),
  KEY `idx_income_status` (`status`),
  CONSTRAINT `fk_income_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_income_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers_ledger` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- GST RECORDS - Tax Management
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gst_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gst_date` (`transaction_date`),
  KEY `idx_gst_type` (`transaction_type`),
  KEY `idx_gst_period` (`gst_return_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- BANK TRANSACTIONS
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bank_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_bank_transaction` (`bank_account_id`),
  KEY `fk_bank_payment` (`payment_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_reconciled` (`is_reconciled`),
  CONSTRAINT `fk_bank_transaction` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bank_payment` FOREIGN KEY (`payment_id`) REFERENCES `accounting_payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- LOANS MANAGEMENT
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_number` varchar(50) NOT NULL UNIQUE,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_loan_bank` (`bank_account_id`),
  KEY `idx_loan_status` (`status`),
  KEY `idx_loan_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_loan_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- LOAN EMI SCHEDULE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loan_emi_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_emi_loan` (`loan_id`),
  KEY `fk_emi_payment` (`payment_id`),
  KEY `idx_emi_due_date` (`due_date`),
  KEY `idx_emi_status` (`status`),
  CONSTRAINT `fk_emi_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_emi_payment` FOREIGN KEY (`payment_id`) REFERENCES `accounting_payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;