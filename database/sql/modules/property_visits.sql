-- Create property_visits table
CREATE TABLE IF NOT EXISTS property_visits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    property_id INT NOT NULL,
    lead_id INT,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled', 'no_show') DEFAULT 'scheduled',
    feedback TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_visit_datetime (visit_date, visit_time),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create visit_reminders table for automated reminders
CREATE TABLE IF NOT EXISTS visit_reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    visit_id INT NOT NULL,
    reminder_type ENUM('24h_before', '1h_before', 'feedback_request') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES property_visits(id) ON DELETE CASCADE,
    INDEX idx_reminder_status (status, scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create visit_availability table for managing available time slots
CREATE TABLE IF NOT EXISTS visit_availability (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    day_of_week TINYINT NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_visits_per_slot INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (property_id, day_of_week, start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default availability (Mon-Sat, 10 AM to 5 PM)
INSERT INTO visit_availability (property_id, day_of_week, start_time, end_time)
SELECT 
    p.id,
    d.day,
    '10:00:00',
    '17:00:00'
FROM 
    properties p
CROSS JOIN 
    (SELECT 1 as day UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) d
WHERE 
    p.status = 'available'
ON DUPLICATE KEY UPDATE
    start_time = VALUES(start_time),
    end_time = VALUES(end_time);
