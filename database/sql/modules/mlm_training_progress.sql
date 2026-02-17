CREATE TABLE IF NOT EXISTS mlm_training_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    progress_percentage INT DEFAULT 0,
    completed_at DATETIME NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progress (associate_id, module_id)
);