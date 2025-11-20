-- Create system_alerts table
CREATE TABLE IF NOT EXISTS system_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level ENUM('info', 'warning', 'critical') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    system VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    acknowledged_by INT NULL,
    resolved_at TIMESTAMP NULL,
    resolved_by INT NULL,
    resolution_notes TEXT NULL,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    INDEX idx_system_alerts_level (level),
    INDEX idx_system_alerts_system (system),
    INDEX idx_system_alerts_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create alert_subscriptions table
CREATE TABLE IF NOT EXISTS alert_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    system VARCHAR(50) NOT NULL,
    level ENUM('info', 'warning', 'critical') NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_subscription (user_id, system, level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create alert_history table for analytics
CREATE TABLE IF NOT EXISTS alert_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    user_id INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alert_id) REFERENCES system_alerts(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_alert_history_alert_id (alert_id),
    INDEX idx_alert_history_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default alert subscriptions for admins
INSERT INTO alert_subscriptions (user_id, system, level)
SELECT id, 'system', 'critical'
FROM users
WHERE role = 'admin'
ON DUPLICATE KEY UPDATE email_enabled = TRUE;
