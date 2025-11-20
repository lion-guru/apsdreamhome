-- Create alert_escalations table
CREATE TABLE IF NOT EXISTS alert_escalations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    level INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alert_id) REFERENCES system_alerts(id),
    INDEX idx_alert_escalations_alert_id (alert_id),
    INDEX idx_alert_escalations_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create alert_assignments table
CREATE TABLE IF NOT EXISTS alert_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    user_id INT NULL,
    team_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alert_id) REFERENCES system_alerts(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (team_id) REFERENCES teams(id),
    INDEX idx_alert_assignments_alert_id (alert_id),
    INDEX idx_alert_assignments_user_id (user_id),
    INDEX idx_alert_assignments_team_id (team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create teams table if not exists
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    lead_id INT NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_teams_lead_id (lead_id),
    INDEX idx_teams_department_id (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create departments table if not exists
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    head_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_id) REFERENCES users(id),
    INDEX idx_departments_head_id (head_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add notification preferences to users table
ALTER TABLE users
ADD COLUMN sms_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN phone VARCHAR(20) NULL;
