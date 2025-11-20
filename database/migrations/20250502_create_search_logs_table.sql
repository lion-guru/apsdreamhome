-- Create search_logs table for tracking property search queries
CREATE TABLE IF NOT EXISTS search_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    
    INDEX idx_query (query),
    INDEX idx_searched_at (searched_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create an index to optimize search performance
CREATE INDEX idx_search_performance ON search_logs (query, results_count, searched_at);

-- Optional: Add a trigger to automatically anonymize sensitive data after a certain period
DELIMITER //
CREATE TRIGGER anonymize_search_logs
BEFORE INSERT ON search_logs
FOR EACH ROW
BEGIN
    -- Anonymize IP address after 90 days
    IF NEW.searched_at < DATE_SUB(NOW(), INTERVAL 90 DAY) THEN
        SET NEW.ip_address = MD5(NEW.ip_address);
    END IF;
END;//
DELIMITER ;

-- Add a comment to explain the table's purpose
ALTER TABLE search_logs COMMENT 'Tracks user search queries for analytics and improvement';
