-- Table: associates
CREATE TABLE IF NOT EXISTS associates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    post VARCHAR(50),
    business_volume DECIMAL(15,2) DEFAULT 0,
    parent_id INT,
    join_date DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (parent_id) REFERENCES associates(id)
);

-- Table: payout_slabs
CREATE TABLE IF NOT EXISTS payout_slabs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post VARCHAR(50) NOT NULL,
    min_business DECIMAL(15,2) NOT NULL,
    max_business DECIMAL(15,2) NOT NULL,
    percent DECIMAL(5,2) NOT NULL,
    reward VARCHAR(100)
);

-- Table: sales
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    date DATE NOT NULL,
    booking_id VARCHAR(50),
    FOREIGN KEY (associate_id) REFERENCES associates(id)
);

-- Table: payouts
CREATE TABLE IF NOT EXISTS payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT NOT NULL,
    sale_id INT NOT NULL,
    payout_amount DECIMAL(15,2) NOT NULL,
    payout_percent DECIMAL(5,2) NOT NULL,
    period VARCHAR(20),
    generated_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','approved','paid') DEFAULT 'pending',
    FOREIGN KEY (associate_id) REFERENCES associates(id),
    FOREIGN KEY (sale_id) REFERENCES sales(id)
);
