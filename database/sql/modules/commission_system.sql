-- APS Dream Homes - Advanced MLM Commission System Database
-- This system provides comprehensive commission tracking and payouts

-- Commission records table
CREATE TABLE IF NOT EXISTS mlm_commission_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    customer_id INT NOT NULL,
    booking_amount DECIMAL(15,2) NOT NULL,
    commission_details JSON NOT NULL,
    total_commission DECIMAL(12,2) NOT NULL,
    status ENUM('calculated', 'approved', 'paid', 'cancelled') DEFAULT 'calculated',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_associate_status (associate_id, status),
    INDEX idx_created_at (created_at)
);

-- Individual commission entries
CREATE TABLE IF NOT EXISTS mlm_commissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    downline_id INT,
    commission_amount DECIMAL(12,2) NOT NULL,
    commission_type ENUM('direct', 'team', 'level_bonus', 'matching_bonus', 'leadership_bonus', 'performance_bonus', 'rank_advance') NOT NULL,
    status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (downline_id) REFERENCES mlm_agents(id) ON DELETE SET NULL,
    INDEX idx_associate_type (associate_id, commission_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Commission payout records
CREATE TABLE IF NOT EXISTS mlm_payouts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    commission_ids TEXT NOT NULL, -- Comma-separated commission IDs
    payout_date DATE NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    payment_method ENUM('bank_transfer', 'cheque', 'cash', 'online') DEFAULT 'bank_transfer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_associate_date (associate_id, payout_date),
    INDEX idx_status (status)
);

-- Commission targets and achievements
CREATE TABLE IF NOT EXISTS mlm_commission_targets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    target_period ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    achieved_amount DECIMAL(15,2) DEFAULT 0,
    target_type ENUM('personal_sales', 'team_sales', 'recruitment') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reward_amount DECIMAL(10,2),
    status ENUM('active', 'achieved', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_associate_period (associate_id, target_period),
    INDEX idx_status (status)
);

-- Rank advancement bonuses
CREATE TABLE IF NOT EXISTS mlm_rank_advancements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    previous_level VARCHAR(50) NOT NULL,
    new_level VARCHAR(50) NOT NULL,
    bonus_amount DECIMAL(10,2) NOT NULL,
    payout_status ENUM('pending', 'paid') DEFAULT 'pending',
    advancement_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_associate_date (associate_id, advancement_date)
);

-- Commission withdrawal requests
CREATE TABLE IF NOT EXISTS mlm_withdrawal_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    available_balance DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending',
    request_date DATE NOT NULL,
    processed_date DATE,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_associate_status (associate_id, status)
);

-- Commission analytics
CREATE TABLE IF NOT EXISTS mlm_commission_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    period_date DATE NOT NULL,
    total_earned DECIMAL(12,2) NOT NULL,
    total_paid DECIMAL(12,2) NOT NULL,
    pending_amount DECIMAL(12,2) NOT NULL,
    direct_commissions DECIMAL(10,2),
    team_commissions DECIMAL(10,2),
    bonus_commissions DECIMAL(10,2),
    rank_advances DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_associate_period (associate_id, period_date),
    UNIQUE KEY unique_associate_period (associate_id, period_date)
);

-- Insert sample commission structure data
INSERT INTO mlm_commission_targets (associate_id, target_period, target_amount, target_type, start_date, end_date, reward_amount, status)
SELECT
    id as associate_id,
    'monthly' as target_period,
    CASE
        WHEN current_level = 'Associate' THEN 500000
        WHEN current_level = 'Sr. Associate' THEN 1500000
        WHEN current_level = 'BDM' THEN 3000000
        WHEN current_level = 'Sr. BDM' THEN 5000000
        WHEN current_level = 'Vice President' THEN 8000000
        WHEN current_level = 'President' THEN 12000000
        WHEN current_level = 'Site Manager' THEN 20000000
        ELSE 1000000
    END as target_amount,
    'personal_sales' as target_type,
    DATE_FORMAT(CURDATE(), '%Y-%m-01') as start_date,
    LAST_DAY(CURDATE()) as end_date,
    CASE
        WHEN current_level = 'Associate' THEN 5000
        WHEN current_level = 'Sr. Associate' THEN 15000
        WHEN current_level = 'BDM' THEN 30000
        WHEN current_level = 'Sr. BDM' THEN 50000
        WHEN current_level = 'Vice President' THEN 80000
        WHEN current_level = 'President' THEN 120000
        WHEN current_level = 'Site Manager' THEN 200000
        ELSE 10000
    END as reward_amount,
    'active' as status
FROM mlm_agents
WHERE status = 'active'
ON DUPLICATE KEY UPDATE
    target_amount = VALUES(target_amount),
    reward_amount = VALUES(reward_amount),
    status = 'active';

-- Create indexes for better performance
CREATE INDEX idx_commission_records_associate ON mlm_commission_records(associate_id, status);
CREATE INDEX idx_commissions_associate_type ON mlm_commissions(associate_id, commission_type, status);
CREATE INDEX idx_payouts_associate_date ON mlm_payouts(associate_id, payout_date, status);
CREATE INDEX idx_targets_associate_period ON mlm_commission_targets(associate_id, target_period, status);
