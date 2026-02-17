-- 1. Land Records (Kisan Management)
CREATE TABLE IF NOT EXISTS land_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_name VARCHAR(100) NOT NULL,
    seller_contact VARCHAR(50),
    location VARCHAR(255),
    area DECIMAL(10,2),
    purchase_price DECIMAL(15,2),
    amount_paid DECIMAL(15,2) DEFAULT 0,
    amount_due DECIMAL(15,2) DEFAULT 0,
    purchase_date DATE,
    registry_status ENUM('registry','agreement') DEFAULT 'agreement',
    site_name VARCHAR(100),
    engineer_name VARCHAR(100),
    site_map_file VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Sites/Colonies
CREATE TABLE IF NOT EXISTS sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(100) NOT NULL,
    land_id INT,
    map_file VARCHAR(255),
    engineer_name VARCHAR(100),
    FOREIGN KEY (land_id) REFERENCES land_records(id)
);

-- 3. Associates (Agents/Downline)
CREATE TABLE IF NOT EXISTS associates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(50),
    commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    parent_id INT DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES associates(id)
);

-- 4. Customers
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(50),
    address VARCHAR(255),
    email VARCHAR(100),
    kyc_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Plots
CREATE TABLE IF NOT EXISTS plots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT,
    plot_number VARCHAR(20) NOT NULL,
    size VARCHAR(50),
    facing VARCHAR(20),
    status ENUM('available','booked','sold') NOT NULL DEFAULT 'available',
    customer_id INT DEFAULT NULL,
    associate_id INT DEFAULT NULL,
    sale_price DECIMAL(15,2),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (associate_id) REFERENCES associates(id)
);

-- 6. Plot Sales (with EMI/payment tracking)
CREATE TABLE IF NOT EXISTS plot_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_id INT,
    customer_id INT,
    associate_id INT,
    sale_price DECIMAL(15,2),
    payment_mode ENUM('full','emi') DEFAULT 'full',
    emi_total DECIMAL(15,2) DEFAULT 0,
    emi_paid DECIMAL(15,2) DEFAULT 0,
    emi_due DECIMAL(15,2) DEFAULT 0,
    sale_date DATE,
    FOREIGN KEY (plot_id) REFERENCES plots(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (associate_id) REFERENCES associates(id)
);

-- 7. Payouts (Level-wise Commission Distribution)
CREATE TABLE IF NOT EXISTS payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_sale_id INT,
    associate_id INT,
    level INT,
    commission_percent DECIMAL(5,2),
    payout_amount DECIMAL(15,2),
    payout_date DATE,
    status ENUM('pending','paid') DEFAULT 'pending',
    FOREIGN KEY (plot_sale_id) REFERENCES plot_sales(id),
    FOREIGN KEY (associate_id) REFERENCES associates(id)
);

-- 8. Investors
CREATE TABLE IF NOT EXISTS investors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    contact VARCHAR(50),
    invested_amount DECIMAL(15,2),
    returns_paid DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Investments (links investors to sites/plots)
CREATE TABLE IF NOT EXISTS investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investor_id INT,
    site_id INT,
    plot_id INT DEFAULT NULL,
    amount DECIMAL(15,2),
    invested_on DATE,
    FOREIGN KEY (investor_id) REFERENCES investors(id),
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (plot_id) REFERENCES plots(id)
);

-- 10. Transactions (Full Money Trail)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('in','out') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    from_entity VARCHAR(100),
    to_entity VARCHAR(100),
    reason VARCHAR(255),
    related_table VARCHAR(50),
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
