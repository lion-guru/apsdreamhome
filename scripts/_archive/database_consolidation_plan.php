<?php
/**
 * DATABASE CONSOLIDATION MASTER PLAN
 * 
 * Project: APS Dream Home
 * Purpose: Complete Real Estate ERP + CRM + MLM + AI Platform
 * 
 * Architecture Overview:
 * ====================
 * 
 * 1. CORE MODULES:
 *    ├── USER_MGMT     → Unified user system (Customer, Agent, Associate, Admin)
 *    ├── CRM_LEADS     → Lead management & scoring
 *    ├── PROPERTY      → Property listings (Buy/Rent)
 *    ├── PLOT_LAND     → Colony development & land management
 *    ├── PAYMENT       → Financial transactions
 *    ├── MLM_NETWORK   → Multi-level marketing & referrals
 *    └── AI_ML        → Smart automation & chatbot
 * 
 * 2. USER SYSTEM DESIGN (Unified):
 *    ================================
 *    All users stored in `users` table with `user_type` column:
 *    
 *    user_type values:
 *    ├── 'customer'   → Property buyer/renter
 *    ├── 'associate' → MLM member / referral partner
 *    ├── 'agent'     → Property agent
 *    ├── 'employee'   → Company employee
 *    └── 'admin'     → System administrator
 *    
 *    Additional profile data in related tables:
 *    ├── user_profiles     → Detailed profile (KYC, preferences)
 *    ├── user_addresses    → Multiple addresses
 *    ├── user_bank_accounts → Bank details
 *    ├── user_documents    → KYC documents
 *    └── user_preferences  → Settings & preferences
 * 
 * 3. PROPERTY SYSTEM DESIGN:
 *    =========================
 *    ├── properties     → General listings (like 99acres)
 *    ├── plots         → Colony plots with booking/payment
 *    └── plot_master   → Land records (Gata numbers, development)
 *    
 *    Each serves different purpose:
 *    ├── properties     → Buy/Sell/Rent portal
 *    ├── plots         → Internal colony management
 *    └── plot_master   → Government land records
 * 
 * 4. LEAD SCORING SYSTEM:
 *    ======================
 *    AI-powered lead qualification:
 *    
 *    lead_scores table stores calculated scores based on:
 *    ├── Demographics     → Budget, location, property type
 *    ├── Engagement       → Website visits, form fills, calls
 *    ├── Behavior         → Time on site, pages visited
 *    └── AI Analysis     → Chat conversations, property interest
 *    
 *    Auto-actions based on score:
 *    ├── Score > 80  → Hot lead, auto-assign to sales
 *    ├── Score 50-80 → Warm lead, nurture campaign
 *    └── Score < 50  → Cold lead, periodic follow-up
 * 
 * 5. MLM SYSTEM DESIGN:
 *    ====================
 *    Binary MLM with:
 *    ├── mlm_tree           → Network hierarchy
 *    ├── mlm_commissions    → Commission calculations
 *    ├── mlm_ranks         → Rank advancements
 *    └── mlm_payouts       → Payout processing
 *    
 *    Customer can become Associate → creates MLM account
 * 
 * 6. PLOT/COLONY DEVELOPMENT:
 *    =========================
 *    Full lifecycle management:
 *    ├── plot_master   → Land purchase (Gata numbers)
 *    ├── plots         → Plot booking with payments
 *    ├── plot_development → Roads, parks, drainage
 *    └── plot_allocation → Final plot to customer
 *    
 *    Cost calculation:
 *    ├── Land cost (Gata based)
 *    ├── Development cost (per sqft rate)
 *    ├── Amenities cost
 *    └── Profit margin
 * 
 * CONSOLIDATION ACTIONS:
 * ======================
 * 
 * PHASE 1: SAFE CLEANUP (No data loss)
 * -------------------------------------
 * 1. Delete CACHE_TEMP tables (10 tables)
 * 2. Delete TESTING tables (4 tables)
 * 3. Archive UNKNOWN empty tables (111 tables)
 * 
 * PHASE 2: USER CONSOLIDATION (After testing)
 * ---------------------------------------------
 * 1. Enhance `users` table with additional columns
 * 2. Create `user_profiles` table for detailed info
 * 3. Create `user_addresses` table for multiple addresses
 * 4. Create `user_documents` table for KYC
 * 5. Migrate data from customers/admin_users
 * 6. Update all code to use unified user system
 * 
 * PHASE 3: FEATURE IMPLEMENTATION
 * ---------------------------------
 * 1. Implement lead scoring system
 * 2. Implement automation triggers
 * 3. Implement AI recommendations
 * 4. Implement MLM tree visualization
 * 5. Implement plot development cost calculator
 * 
 * PHASE 4: PERFORMANCE OPTIMIZATION
 * ---------------------------------
 * 1. Add proper indexes
 * 2. Implement query caching
 * 3. Archive old data
 * 4. Add database views for reports
 * 
 * =============================================================================
 */

// Configuration
$config = [
    'database' => 'apsdreamhome',
    'host' => '127.0.0.1',
    'port' => 3307,
    'username' => 'root',
    'password' => '',
];

/**
 * SQL STATEMENTS FOR CONSOLIDATION
 */

// 1. ENHANCED USERS TABLE
$sql_users_table = "
-- Add columns to users table for unification
ALTER TABLE users ADD COLUMN IF NOT EXISTS user_type ENUM('customer', 'associate', 'agent', 'employee', 'admin', 'super_admin') DEFAULT 'customer' AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) AFTER name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) AFTER first_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_birth DATE AFTER last_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female', 'other') AFTER date_of_birth;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(500) AFTER gender;
ALTER TABLE users ADD COLUMN IF NOT EXISTS kyc_status ENUM('pending', 'partial', 'verified', 'rejected') DEFAULT 'pending' AFTER profile_image;
ALTER TABLE users ADD COLUMN IF NOT EXISTS referral_code VARCHAR(20) UNIQUE AFTER kyc_status;
ALTER TABLE users ADD COLUMN IF NOT EXISTS referred_by BIGINT UNSIGNED AFTER referral_code;
ALTER TABLE users ADD COLUMN IF NOT EXISTS mlm_position ENUM('left', 'right', 'none') DEFAULT 'none' AFTER referred_by;
ALTER TABLE users ADD COLUMN IF NOT EXISTS sponsor_id BIGINT UNSIGNED AFTER mlm_position;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP AFTER sponsor_id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0 AFTER last_login_at;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP AFTER login_attempts;
ALTER TABLE users ADD COLUMN IF NOT EXISTS metadata JSON AFTER locked_until;
ALTER TABLE users ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP AFTER metadata;
ALTER TABLE users ADD COLUMN IF NOT EXISTS deleted_by BIGINT UNSIGNED AFTER deleted_at;
";

// 2. USER ADDRESSES TABLE
$sql_user_addresses = "
CREATE TABLE IF NOT EXISTS user_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    address_type ENUM('permanent', 'current', 'billing', 'shipping', 'office') DEFAULT 'permanent',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    landmark VARCHAR(100),
    city VARCHAR(100),
    district VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'India',
    pincode VARCHAR(10),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_address_type (address_type),
    INDEX idx_city_state (city, state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 3. USER BANK ACCOUNTS TABLE
$sql_user_bank_accounts = "
CREATE TABLE IF NOT EXISTS user_bank_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    account_type ENUM('savings', 'current', 'joint') DEFAULT 'savings',
    account_holder_name VARCHAR(200) NOT NULL,
    account_number VARCHAR(30) NOT NULL,
    bank_name VARCHAR(200) NOT NULL,
    branch_name VARCHAR(200),
    ifsc_code VARCHAR(20) NOT NULL,
    micr_code VARCHAR(20),
    is_primary TINYINT(1) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 4. USER DOCUMENTS TABLE
$sql_user_documents = "
CREATE TABLE IF NOT EXISTS user_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('aadhar', 'pan', 'passport', 'voter_id', 'driving_license', 'bank_passbook', 'cheque', 'photo', 'other') NOT NULL,
    document_number VARCHAR(50),
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    verified_by BIGINT UNSIGNED,
    verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_doc_type (document_type),
    INDEX idx_verification (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 5. LEAD SCORING TABLE
$sql_lead_scoring = "
CREATE TABLE IF NOT EXISTS lead_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    total_score INT NOT NULL DEFAULT 0,
    demographics_score INT DEFAULT 0,
    engagement_score INT DEFAULT 0,
    behavior_score INT DEFAULT 0,
    ai_analysis_score INT DEFAULT 0,
    score_factors JSON,
    rank ENUM('cold', 'warm', 'hot', 'hot_plus') DEFAULT 'cold',
    is_hot_lead TINYINT(1) DEFAULT 0,
    auto_assign_at TIMESTAMP,
    assigned_to BIGINT UNSIGNED,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead_id (lead_id),
    INDEX idx_total_score (total_score),
    INDEX idx_rank (rank),
    INDEX idx_is_hot (is_hot_lead)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 6. PLOT DEVELOPMENT COSTS TABLE
$sql_plot_development = "
CREATE TABLE IF NOT EXISTS plot_development_costs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colony_id INT UNSIGNED NOT NULL,
    cost_type ENUM('land', 'development', 'amenities', 'legal', 'misc') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(12,2) NOT NULL,
    per_sqft_rate DECIMAL(10,2),
    total_area_sqft DECIMAL(12,2),
    vendor_name VARCHAR(200),
    invoice_number VARCHAR(50),
    invoice_date DATE,
    payment_status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
    paid_amount DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_id (colony_id),
    INDEX idx_cost_type (cost_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 7. AUTOMATION TRIGGERS TABLE
$sql_automation_triggers = "
CREATE TABLE IF NOT EXISTS automation_triggers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trigger_name VARCHAR(100) NOT NULL,
    trigger_type ENUM('lead_score', 'lead_status', 'payment', 'property', 'schedule', 'custom') NOT NULL,
    conditions JSON NOT NULL,
    actions JSON NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    last_triggered_at TIMESTAMP,
    trigger_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_trigger_type (trigger_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 8. PLOT ALLOCATION TABLE (for customer plot finalization)
$sql_plot_allocation = "
CREATE TABLE IF NOT EXISTS plot_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plot_id INT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    allocation_date DATE NOT NULL,
    agreement_number VARCHAR(50),
    agreement_date DATE,
    registry_number VARCHAR(50),
    registry_date DATE,
    registry_amount DECIMAL(12,2),
    plot_rate DECIMAL(10,2) NOT NULL,
    total_plot_value DECIMAL(12,2) NOT NULL,
    development_charges DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) DEFAULT 0,
    amount_pending DECIMAL(12,2),
    possession_date DATE,
    possession_status ENUM('not_due', 'due', 'given', 'delayed') DEFAULT 'not_due',
    status ENUM('booked', 'agreement_pending', 'agreement_done', 'registry_pending', 'registry_done', 'completed') DEFAULT 'booked',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plot_id (plot_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

echo "Database Consolidation Plan Generated Successfully!\n";
echo "\nReview the SQL statements above and execute them carefully.\n";
