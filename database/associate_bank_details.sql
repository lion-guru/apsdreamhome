CREATE TABLE IF NOT EXISTS associate_bank_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT NOT NULL,
    account_holder VARCHAR(100),
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    ifsc VARCHAR(20),
    branch VARCHAR(100),
    pan VARCHAR(20),
    aadhaar VARCHAR(20),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
);
