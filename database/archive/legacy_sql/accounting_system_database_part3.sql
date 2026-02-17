-- =========================================
-- COMPREHENSIVE ACCOUNTING SYSTEM DATABASE - PART 3
-- Financial Reporting and Advanced Features
-- =========================================

-- --------------------------------------------------------
-- BANK RECONCILIATION
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bank_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_reconciliation_bank` (`bank_account_id`),
  KEY `idx_reconciliation_date` (`reconciliation_date`),
  CONSTRAINT `fk_reconciliation_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- FINANCIAL REPORTS CACHE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `financial_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` enum('profit_loss','balance_sheet','cash_flow','trial_balance','ledger','aging','gst_summary') NOT NULL,
  `report_period` varchar(50) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `report_data` longtext NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_cached` tinyint(1) DEFAULT 1,
  `cache_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_report_type` (`report_type`),
  KEY `idx_report_period` (`report_period`),
  KEY `idx_report_dates` (`from_date`, `to_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- RECURRING TRANSACTIONS
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recurring_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_recurring_account` (`account_id`),
  KEY `idx_recurring_due_date` (`next_due_date`),
  KEY `idx_recurring_status` (`status`),
  CONSTRAINT `fk_recurring_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- BUDGET PLANNING
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `budget_planning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_budget_account` (`account_id`),
  KEY `idx_budget_year` (`budget_year`),
  KEY `idx_budget_period` (`period_start`, `period_end`),
  CONSTRAINT `fk_budget_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- CASH FLOW PROJECTIONS
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cash_flow_projections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_projection_date` (`projection_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ACCOUNTING SETTINGS
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `accounting_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- AUDIT TRAIL
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `changed_fields` json DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_table` (`table_name`),
  KEY `idx_audit_record` (`record_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- FINANCIAL YEAR CONFIGURATION
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `financial_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `is_closed` tinyint(1) DEFAULT 0,
  `closing_date` date DEFAULT NULL,
  `opening_balances_set` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `year_name` (`year_name`),
  KEY `idx_fy_dates` (`start_date`, `end_date`),
  KEY `idx_fy_current` (`is_current`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- INSERT DEFAULT ACCOUNTING SETTINGS
-- --------------------------------------------------------
INSERT INTO `accounting_settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_system`) VALUES
('company_name', 'APS Dream Home', 'string', 'Company Name for Financial Reports', 0),
('company_address', 'Company Address Here', 'string', 'Company Address for Reports', 0),
('company_gstin', '', 'string', 'Company GST Number', 0),
('company_pan', '', 'string', 'Company PAN Number', 0),
('default_currency', 'INR', 'string', 'Default Currency Code', 1),
('decimal_places', '2', 'integer', 'Decimal Places for Amounts', 1),
('financial_year_start', '04-01', 'string', 'Financial Year Start (MM-DD)', 1),
('gst_enabled', '1', 'boolean', 'Enable GST Calculations', 1),
('auto_backup_enabled', '1', 'boolean', 'Enable Automatic Database Backup', 1),
('invoice_prefix', 'INV', 'string', 'Invoice Number Prefix', 0),
('expense_approval_required', '1', 'boolean', 'Require Approval for Expenses', 0),
('bank_reconciliation_reminder', '7', 'integer', 'Bank Reconciliation Reminder Days', 0);

-- --------------------------------------------------------
-- INSERT DEFAULT CHART OF ACCOUNTS
-- --------------------------------------------------------
INSERT INTO `chart_of_accounts` (`account_code`, `account_name`, `account_type`, `account_category`, `parent_account_id`, `description`) VALUES
('1000', 'ASSETS', 'asset', 'current_asset', NULL, 'All Asset Accounts'),
('1100', 'Current Assets', 'asset', 'current_asset', 1, 'Current Assets Group'),
('1110', 'Cash in Hand', 'asset', 'current_asset', 2, 'Physical Cash'),
('1120', 'Bank Accounts', 'asset', 'current_asset', 2, 'All Bank Accounts'),
('1130', 'Accounts Receivable', 'asset', 'current_asset', 2, 'Customer Outstanding'),
('1140', 'Inventory', 'asset', 'current_asset', 2, 'Stock/Inventory'),
('1200', 'Fixed Assets', 'asset', 'fixed_asset', 1, 'Fixed Assets Group'),
('1210', 'Land and Building', 'asset', 'fixed_asset', 7, 'Property Assets'),
('1220', 'Furniture and Fixtures', 'asset', 'fixed_asset', 7, 'Office Furniture'),
('1230', 'Computer Equipment', 'asset', 'fixed_asset', 7, 'IT Equipment'),
('2000', 'LIABILITIES', 'liability', 'current_liability', NULL, 'All Liability Accounts'),
('2100', 'Current Liabilities', 'liability', 'current_liability', 11, 'Current Liabilities Group'),
('2110', 'Accounts Payable', 'liability', 'current_liability', 12, 'Supplier Outstanding'),
('2120', 'Short Term Loans', 'liability', 'current_liability', 12, 'Short Term Borrowings'),
('2130', 'Tax Payable', 'liability', 'current_liability', 12, 'Tax Obligations'),
('2200', 'Long Term Liabilities', 'liability', 'long_term_liability', 11, 'Long Term Liabilities'),
('2210', 'Long Term Loans', 'liability', 'long_term_liability', 16, 'Long Term Borrowings'),
('3000', 'EQUITY', 'equity', 'owner_equity', NULL, 'Owner Equity Accounts'),
('3100', 'Capital', 'equity', 'owner_equity', 18, 'Owner Capital'),
('3200', 'Retained Earnings', 'equity', 'owner_equity', 18, 'Accumulated Profits'),
('4000', 'INCOME', 'income', 'revenue', NULL, 'All Income Accounts'),
('4100', 'Sales Revenue', 'income', 'revenue', 21, 'Primary Sales Income'),
('4200', 'Other Income', 'income', 'revenue', 21, 'Other Revenue Sources'),
('5000', 'EXPENSES', 'expense', 'operating_expense', NULL, 'All Expense Accounts'),
('5100', 'Operating Expenses', 'expense', 'operating_expense', 24, 'Regular Operating Costs'),
('5110', 'Office Rent', 'expense', 'operating_expense', 25, 'Office Rental Expenses'),
('5120', 'Salaries and Wages', 'expense', 'operating_expense', 25, 'Employee Compensation'),
('5130', 'Utilities', 'expense', 'operating_expense', 25, 'Electricity, Water, etc'),
('5140', 'Marketing Expenses', 'expense', 'operating_expense', 25, 'Advertising and Promotion'),
('5200', 'Administrative Expenses', 'expense', 'operating_expense', 24, 'Admin and General Expenses'),
('5210', 'Professional Fees', 'expense', 'operating_expense', 30, 'Legal, Audit, Consulting'),
('5220', 'Insurance', 'expense', 'operating_expense', 30, 'Insurance Premiums'),
('5300', 'Financial Expenses', 'expense', 'non_operating_expense', 24, 'Interest and Financial Costs'),
('5310', 'Interest Expense', 'expense', 'non_operating_expense', 33, 'Loan Interest Payments'),
('5320', 'Bank Charges', 'expense', 'non_operating_expense', 33, 'Banking Fees and Charges');

-- --------------------------------------------------------
-- CREATE CURRENT FINANCIAL YEAR
-- --------------------------------------------------------
INSERT INTO `financial_years` (`year_name`, `start_date`, `end_date`, `is_current`, `created_by`) VALUES
('2024-25', '2024-04-01', '2025-03-31', 1, 1);