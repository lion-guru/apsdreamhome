-- APS Dream Homes - Hybrid Real Estate MLM System
-- Supports both company properties (colony plotting) and resell properties

-- Property types table
CREATE TABLE IF NOT EXISTS real_estate_properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_code VARCHAR(50) UNIQUE NOT NULL,
    property_name VARCHAR(255) NOT NULL,
    property_type ENUM('company', 'resell') NOT NULL,
    property_category ENUM('plot', 'flat', 'house', 'commercial', 'land') NOT NULL,
    location VARCHAR(255) NOT NULL,
    area_sqft DECIMAL(10,2) NOT NULL,
    rate_per_sqft DECIMAL(10,2) NOT NULL,
    total_value DECIMAL(15,2) NOT NULL,
    development_cost DECIMAL(15,2) DEFAULT 0,
    commission_percentage DECIMAL(5,2) NOT NULL,
    status ENUM('available', 'booked', 'sold', 'cancelled') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_property_type (property_type),
    INDEX idx_category (property_category),
    INDEX idx_status (status),
    INDEX idx_location (location)
);

-- Development cost breakdown
CREATE TABLE IF NOT EXISTS property_development_costs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    cost_type ENUM('land_cost', 'construction', 'infrastructure', 'legal', 'marketing', 'commission', 'other') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL,
    percentage_of_total DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES real_estate_properties(id) ON DELETE CASCADE,
    INDEX idx_property_cost_type (property_id, cost_type)
);

-- Hybrid commission plans
CREATE TABLE IF NOT EXISTS hybrid_commission_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(255) NOT NULL,
    plan_code VARCHAR(50) UNIQUE NOT NULL,
    plan_type ENUM('company_mlm', 'resell_fixed', 'hybrid') NOT NULL,
    description TEXT,
    total_commission_percentage DECIMAL(5,2) NOT NULL,
    company_commission_percentage DECIMAL(5,2) DEFAULT 0,
    resell_commission_percentage DECIMAL(5,2) DEFAULT 0,
    development_cost_included BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_plan_type (plan_type),
    INDEX idx_status (status)
);

-- Company property levels (MLM structure)
CREATE TABLE IF NOT EXISTS company_property_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    level_name VARCHAR(100) NOT NULL,
    level_order INT NOT NULL,
    direct_commission_percentage DECIMAL(5,2) NOT NULL,
    team_commission_percentage DECIMAL(5,2) DEFAULT 0,
    level_bonus_percentage DECIMAL(5,2) DEFAULT 0,
    matching_bonus_percentage DECIMAL(5,2) DEFAULT 0,
    leadership_bonus_percentage DECIMAL(5,2) DEFAULT 0,
    monthly_target DECIMAL(15,2) NOT NULL,
    min_plot_value DECIMAL(15,2) DEFAULT 0,
    max_plot_value DECIMAL(15,2) DEFAULT 999999999,
    FOREIGN KEY (plan_id) REFERENCES hybrid_commission_plans(id) ON DELETE CASCADE,
    INDEX idx_plan_level (plan_id, level_order)
);

-- Resell property commission structure
CREATE TABLE IF NOT EXISTS resell_commission_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    property_category ENUM('plot', 'flat', 'house', 'commercial', 'land') NOT NULL,
    min_value DECIMAL(15,2) NOT NULL,
    max_value DECIMAL(15,2) NOT NULL,
    commission_percentage DECIMAL(5,2) NOT NULL,
    fixed_commission DECIMAL(10,2) DEFAULT 0,
    commission_type ENUM('percentage', 'fixed', 'both') DEFAULT 'percentage',
    FOREIGN KEY (plan_id) REFERENCES hybrid_commission_plans(id) ON DELETE CASCADE,
    INDEX idx_plan_category (plan_id, property_category)
);

-- Plot rate calculation history
CREATE TABLE IF NOT EXISTS plot_rate_calculations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    land_cost DECIMAL(15,2) NOT NULL,
    development_cost DECIMAL(15,2) NOT NULL,
    total_commission DECIMAL(15,2) NOT NULL,
    profit_margin DECIMAL(5,2) NOT NULL,
    final_rate_per_sqft DECIMAL(10,2) NOT NULL,
    calculated_by INT NOT NULL,
    calculation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES real_estate_properties(id) ON DELETE CASCADE,
    FOREIGN KEY (calculated_by) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    INDEX idx_calculation_date (calculation_date)
);

-- Hybrid commission records
CREATE TABLE IF NOT EXISTS hybrid_commission_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    property_id INT NOT NULL,
    customer_id INT,
    sale_amount DECIMAL(15,2) NOT NULL,
    commission_amount DECIMAL(12,2) NOT NULL,
    commission_type ENUM('company_mlm', 'resell_fixed', 'direct') NOT NULL,
    commission_breakdown JSON,
    level_achieved VARCHAR(100),
    payout_status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES real_estate_properties(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_associate_type (associate_id, commission_type),
    INDEX idx_payout_status (payout_status),
    INDEX idx_created_at (created_at)
);

-- Sample data for hybrid commission plans
INSERT INTO hybrid_commission_plans (plan_name, plan_code, plan_type, description, total_commission_percentage, company_commission_percentage, resell_commission_percentage, development_cost_included, status, created_by) VALUES
('Hybrid Real Estate Plan V1', 'HYBRID_V1', 'hybrid', 'Complete hybrid plan for company plotting and resell properties with 20% total commission', 20.00, 15.00, 5.00, TRUE, 'active', 1),
('Company Only MLM', 'COMPANY_MLM_V1', 'company_mlm', 'MLM structure only for company developed properties', 15.00, 15.00, 0.00, TRUE, 'active', 1),
('Resell Commission Plan', 'RESELL_V1', 'resell_fixed', 'Fixed commission structure for resell properties', 5.00, 0.00, 5.00, FALSE, 'active', 1);

-- Company property levels for MLM
INSERT INTO company_property_levels (plan_id, level_name, level_order, direct_commission_percentage, team_commission_percentage, level_bonus_percentage, matching_bonus_percentage, leadership_bonus_percentage, monthly_target, min_plot_value, max_plot_value) VALUES
(1, 'Associate', 1, 6.00, 2.00, 0.00, 0.00, 0.00, 1000000.00, 0.00, 10000000.00),
(1, 'Sr. Associate', 2, 8.00, 3.00, 1.00, 2.00, 0.00, 3500000.00, 10000000.00, 50000000.00),
(1, 'BDM', 3, 10.00, 4.00, 2.00, 3.00, 1.00, 7000000.00, 50000000.00, 150000000.00),
(1, 'Sr. BDM', 4, 12.00, 5.00, 3.00, 4.00, 2.00, 15000000.00, 150000000.00, 500000000.00),
(1, 'Vice President', 5, 15.00, 6.00, 4.00, 5.00, 3.00, 30000000.00, 500000000.00, 1000000000.00),
(1, 'President', 6, 18.00, 7.00, 5.00, 6.00, 4.00, 50000000.00, 1000000000.00, 9999999999.00),
(1, 'Site Manager', 7, 20.00, 8.00, 6.00, 7.00, 5.00, 100000000.00, 10000000000.00, 99999999999.00);

-- Resell commission structure
INSERT INTO resell_commission_structure (plan_id, property_category, min_value, max_value, commission_percentage, fixed_commission, commission_type) VALUES
(1, 'plot', 0.00, 10000000.00, 3.00, 0.00, 'percentage'),
(1, 'plot', 10000000.00, 50000000.00, 4.00, 0.00, 'percentage'),
(1, 'plot', 50000000.00, 999999999.00, 5.00, 0.00, 'percentage'),
(1, 'flat', 0.00, 50000000.00, 2.00, 0.00, 'percentage'),
(1, 'flat', 50000000.00, 999999999.00, 3.00, 0.00, 'percentage'),
(1, 'house', 0.00, 999999999.00, 3.00, 0.00, 'percentage'),
(1, 'commercial', 0.00, 999999999.00, 4.00, 0.00, 'percentage'),
(1, 'land', 0.00, 999999999.00, 2.00, 0.00, 'percentage');

-- Sample properties
INSERT INTO real_estate_properties (property_code, property_name, property_type, property_category, location, area_sqft, rate_per_sqft, total_value, development_cost, commission_percentage, status) VALUES
('PLOT-001', 'Green Valley Plot A1', 'company', 'plot', 'Sector 15, Gurgaon', 1000.00, 5000.00, 5000000.00, 2000000.00, 15.00, 'available'),
('PLOT-002', 'Sunrise Colony Plot B5', 'company', 'plot', 'Sector 22, Noida', 1500.00, 4500.00, 6750000.00, 2500000.00, 15.00, 'available'),
('RESELL-001', 'DLF Phase 2 Flat', 'resell', 'flat', 'DLF Phase 2, Gurgaon', 1200.00, 15000.00, 18000000.00, 0.00, 3.00, 'available'),
('RESELL-002', 'Independent House', 'resell', 'house', 'Sector 45, Noida', 2000.00, 8000.00, 16000000.00, 0.00, 3.00, 'available');
