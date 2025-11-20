-- Foreclosure Logs Table Migration
CREATE TABLE IF NOT EXISTS foreclosure_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    emi_plan_id INT NOT NULL,
    status ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    message TEXT,
    foreclosure_amount DECIMAL(12, 2) DEFAULT 0.00,
    additional_data JSON,
    attempted_by INT,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (emi_plan_id) REFERENCES emi_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (attempted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create an index for performance optimization
CREATE INDEX idx_foreclosure_logs_emi_plan ON foreclosure_logs(emi_plan_id);
CREATE INDEX idx_foreclosure_logs_status ON foreclosure_logs(status);
CREATE INDEX idx_foreclosure_logs_attempted_at ON foreclosure_logs(attempted_at);
