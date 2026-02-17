-- MLM Commission Plans Management System
-- Allows creating, editing, and managing multiple commission plans

-- Commission plans master table
CREATE TABLE IF NOT EXISTS mlm_commission_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(100) NOT NULL,
    plan_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    plan_type ENUM('standard', 'custom', 'promotional', 'seasonal') DEFAULT 'standard',
    status ENUM('draft', 'active', 'inactive', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activated_at TIMESTAMP NULL,
    deactivated_at TIMESTAMP NULL,
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (created_by) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_type (plan_type),
    INDEX idx_created_by (created_by)
);

-- Plan levels configuration
CREATE TABLE IF NOT EXISTS mlm_plan_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    level_name VARCHAR(50) NOT NULL,
    level_order INT NOT NULL,
    direct_commission DECIMAL(5,2) NOT NULL DEFAULT 0,
    team_commission DECIMAL(5,2) NOT NULL DEFAULT 0,
    level_bonus DECIMAL(5,2) NOT NULL DEFAULT 0,
    matching_bonus DECIMAL(5,2) NOT NULL DEFAULT 0,
    leadership_bonus DECIMAL(5,2) NOT NULL DEFAULT 0,
    performance_bonus DECIMAL(5,2) NOT NULL DEFAULT 0,
    monthly_target DECIMAL(12,2) NOT NULL DEFAULT 0,
    qualification_criteria JSON, -- JSON object with qualification rules
    rewards JSON, -- JSON object with level rewards
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    INDEX idx_plan_level (plan_id, level_order),
    UNIQUE KEY unique_plan_level (plan_id, level_name)
);

-- Plan bonus configurations
CREATE TABLE IF NOT EXISTS mlm_plan_bonuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    bonus_type ENUM('recruitment', 'rank_advance', 'team_building', 'performance', 'loyalty', 'special') NOT NULL,
    bonus_name VARCHAR(100) NOT NULL,
    bonus_description TEXT,
    bonus_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    bonus_fixed_amount DECIMAL(10,2) DEFAULT 0,
    eligibility_criteria JSON, -- Conditions for eligibility
    max_occurrences INT DEFAULT 1, -- How many times this bonus can be earned
    validity_period INT DEFAULT 30, -- Days the bonus is valid
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    INDEX idx_plan_bonus (plan_id, bonus_type)
);

-- Plan calculation rules
CREATE TABLE IF NOT EXISTS mlm_plan_calculation_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    rule_name VARCHAR(100) NOT NULL,
    rule_type ENUM('direct_commission', 'team_commission', 'level_bonus', 'matching_bonus', 'leadership_bonus', 'performance_bonus', 'custom') NOT NULL,
    rule_formula TEXT NOT NULL, -- Mathematical formula for calculation
    rule_conditions JSON, -- Conditions when this rule applies
    priority INT DEFAULT 0, -- Order of execution
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    INDEX idx_plan_rule (plan_id, rule_type, priority)
);

-- Plan version history
CREATE TABLE IF NOT EXISTS mlm_plan_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    version_number VARCHAR(20) NOT NULL,
    version_name VARCHAR(100),
    version_description TEXT,
    changes_summary JSON, -- Summary of changes made
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_rollback_point BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_plan_version (plan_id, version_number)
);

-- Plan performance tracking
CREATE TABLE IF NOT EXISTS mlm_plan_performance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    period_date DATE NOT NULL,
    total_associates INT DEFAULT 0,
    active_associates INT DEFAULT 0,
    total_commissions DECIMAL(15,2) DEFAULT 0,
    total_payouts DECIMAL(15,2) DEFAULT 0,
    avg_commission_per_associate DECIMAL(10,2) DEFAULT 0,
    new_recruits INT DEFAULT 0,
    rank_advancements INT DEFAULT 0,
    performance_score DECIMAL(5,2) DEFAULT 0, -- Calculated performance metric
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    INDEX idx_plan_period (plan_id, period_date),
    UNIQUE KEY unique_plan_period (plan_id, period_date)
);

-- Plan A/B testing
CREATE TABLE IF NOT EXISTS mlm_plan_ab_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_name VARCHAR(100) NOT NULL,
    description TEXT,
    control_plan_id INT NOT NULL,
    variant_plan_id INT NOT NULL,
    target_audience JSON, -- Criteria for which associates get which plan
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('draft', 'running', 'completed', 'cancelled') DEFAULT 'draft',
    winner_plan_id INT,
    results_summary JSON,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (control_plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_plan_id) REFERENCES mlm_commission_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_plan_id) REFERENCES mlm_commission_plans(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

-- Insert default commission plan
INSERT INTO mlm_commission_plans (plan_name, plan_code, description, plan_type, status, created_by, is_default)
VALUES (
    'Standard MLM Plan',
    'STANDARD_V1',
    'Default commission plan with 6 types of commissions',
    'standard',
    'active',
    1,
    TRUE
);

-- Insert levels for default plan
INSERT INTO mlm_plan_levels (plan_id, level_name, level_order, direct_commission, team_commission, level_bonus, matching_bonus, leadership_bonus, performance_bonus, monthly_target, qualification_criteria, rewards)
VALUES
(1, 'Associate', 1, 5.00, 2.00, 0.00, 0.00, 0.00, 0.00, 1000000, '{"personal_sales": 500000}', '{"reward_type": "certificate", "description": "Entry Level Recognition"}'),
(1, 'Sr. Associate', 2, 7.00, 3.00, 2.00, 5.00, 0.00, 0.00, 3500000, '{"personal_sales": 1500000, "team_size": 3}', '{"reward_type": "bonus", "amount": 10000}'),
(1, 'BDM', 3, 10.00, 4.00, 3.00, 8.00, 1.00, 0.00, 7000000, '{"personal_sales": 3000000, "team_size": 10}', '{"reward_type": "gift", "item": "Mobile Phone"}'),
(1, 'Sr. BDM', 4, 12.00, 5.00, 4.00, 10.00, 2.00, 1.00, 15000000, '{"personal_sales": 5000000, "team_size": 25}', '{"reward_type": "trip", "destination": "Domestic Tour"}'),
(1, 'Vice President', 5, 15.00, 6.00, 5.00, 12.00, 3.00, 2.00, 30000000, '{"personal_sales": 8000000, "team_size": 50}', '{"reward_type": "car_fund", "amount": 50000}'),
(1, 'President', 6, 18.00, 7.00, 6.00, 15.00, 4.00, 3.00, 50000000, '{"personal_sales": 12000000, "team_size": 100}', '{"reward_type": "bike", "model": "Premium Bike"}'),
(1, 'Site Manager', 7, 20.00, 8.00, 7.00, 18.00, 5.00, 5.00, 999999999, '{"personal_sales": 20000000, "team_size": 200}', '{"reward_type": "car", "model": "Premium Car"}');

-- Insert default bonuses
INSERT INTO mlm_plan_bonuses (plan_id, bonus_type, bonus_name, bonus_description, bonus_percentage, eligibility_criteria, max_occurrences, validity_period)
VALUES
(1, 'recruitment', 'Fast Start Bonus', 'Bonus for quick recruitment', 5.00, '{"recruits_in_period": 5, "period_days": 30}', 1, 60),
(1, 'rank_advance', 'Rank Advancement Bonus', 'Bonus for level promotion', 0.00, '{"previous_level": "Associate", "new_level": "Sr. Associate"}', 1, 90),
(1, 'team_building', 'Team Builder Award', 'Award for building active team', 10.00, '{"active_team_members": 10, "team_sales": 500000}', 3, 180),
(1, 'performance', 'Monthly Target Bonus', 'Bonus for achieving monthly targets', 2.00, '{"target_achievement": 100}', 12, 30);

-- Insert calculation rules
INSERT INTO mlm_plan_calculation_rules (plan_id, rule_name, rule_type, rule_formula, rule_conditions, priority)
VALUES
(1, 'Direct Commission', 'direct_commission', 'booking_amount * direct_commission_rate / 100', '{"level_required": "all"}', 1),
(1, 'Team Commission', 'team_commission', '(booking_amount * team_commission_rate / 100) * team_multiplier', '{"has_direct_recruits": true}', 2),
(1, 'Level Difference Bonus', 'level_bonus', 'booking_amount * level_difference / 100', '{"downline_level_lower": true}', 3),
(1, 'Matching Bonus', 'matching_bonus', 'recruit_sales * matching_rate / 100', '{"recruit_active": true}', 4),
(1, 'Leadership Bonus', 'leadership_bonus', 'total_team_business * leadership_rate / 100', '{"team_size": 25}', 5),
(1, 'Performance Bonus', 'performance_bonus', 'booking_amount * performance_rate / 100', '{"target_achieved": true}', 6);
