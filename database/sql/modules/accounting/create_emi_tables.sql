-- Create EMI plans table
CREATE TABLE IF NOT EXISTS emi_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    customer_id INT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    tenure_months INT NOT NULL,
    emi_amount DECIMAL(12,2) NOT NULL,
    down_payment DECIMAL(12,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'defaulted', 'cancelled') NOT NULL DEFAULT 'active',
    foreclosure_date DATE DEFAULT NULL,
    foreclosure_amount DECIMAL(12,2) DEFAULT NULL,
    foreclosure_payment_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    INDEX idx_property (property_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
);

-- Create EMI installments table
CREATE TABLE IF NOT EXISTS emi_installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    emi_plan_id INT NOT NULL,
    installment_number INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    principal_amount DECIMAL(12,2) NOT NULL,
    interest_amount DECIMAL(12,2) NOT NULL,
    due_date DATE NOT NULL,
    payment_date DATE DEFAULT NULL,
    payment_status ENUM('pending', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    payment_id INT DEFAULT NULL,
    reminder_sent TINYINT(1) DEFAULT 0,
    last_reminder_date DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_emi_plan (emi_plan_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_due_date (due_date)
);

-- Create reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    generated_for_month INT NOT NULL,
    generated_for_year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_month_year (generated_for_month, generated_for_year)
);
