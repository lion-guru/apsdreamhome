-- 0. Audit Log Table for Super Admin/critical actions
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    username VARCHAR(50),
    role VARCHAR(20),
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (admin_id),
    INDEX (action)
) ENGINE=InnoDB;

-- APS Dream Homes Real Estate ERP/CRM Normalized Schema
-- Fixed: Foreign key dependencies order for MySQL strict mode

-- 1. Admin & Roles
CREATE TABLE IF NOT EXISTS admin (
    aid INT AUTO_INCREMENT PRIMARY KEY,
    auser VARCHAR(50) NOT NULL UNIQUE,
    apass VARCHAR(255) NOT NULL,
    role ENUM('admin','superadmin','finance','associate') NOT NULL DEFAULT 'admin',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Users (Customers/Investors)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    type ENUM('customer','investor','tenant') DEFAULT 'customer',
    status ENUM('active','inactive') DEFAULT 'active',
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Associates & MLM
CREATE TABLE IF NOT EXISTS associates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    parent_id INT,
    commission_percent DECIMAL(5,2),
    level INT DEFAULT 1,
    status ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (parent_id) REFERENCES associates(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS associate_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level INT NOT NULL,
    commission_percent DECIMAL(5,2) NOT NULL,
    description VARCHAR(255)
) ENGINE=InnoDB;

-- 4. Land Purchases
CREATE TABLE IF NOT EXISTS land_purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_name VARCHAR(100),
    purchase_date DATE,
    payment_amount DECIMAL(15,2),
    registry_no VARCHAR(100),
    agreement_doc VARCHAR(255),
    site_location VARCHAR(255),
    engineer VARCHAR(100),
    map_doc VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Sites/Colonies (Projects)
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50),
    status ENUM('active','inactive','completed') DEFAULT 'active',
    land_purchase_id INT,
    FOREIGN KEY (land_purchase_id) REFERENCES land_purchases(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. Plots
CREATE TABLE IF NOT EXISTS plots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    plot_no VARCHAR(50) NOT NULL,
    size_sqft DECIMAL(10,2),
    status ENUM('available','booked','sold','rented','resale') DEFAULT 'available',
    customer_id INT,
    associate_id INT,
    sale_id INT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. Bookings/Sales
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_id INT NOT NULL,
    customer_id INT NOT NULL,
    associate_id INT,
    booking_date DATE,
    status ENUM('booked','cancelled','completed') DEFAULT 'booked',
    amount DECIMAL(15,2),
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. EMI & Payments
CREATE TABLE IF NOT EXISTS emi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    due_date DATE,
    amount DECIMAL(15,2),
    paid_amount DECIMAL(15,2) DEFAULT 0,
    status ENUM('pending','paid','overdue') DEFAULT 'pending',
    payment_date DATE,
    payment_mode VARCHAR(50),
    payment_gateway VARCHAR(50),
    payment_ref VARCHAR(100),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    emi_id INT,
    amount DECIMAL(15,2),
    status ENUM('initiated','success','failed') DEFAULT 'initiated',
    gateway VARCHAR(50),
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (emi_id) REFERENCES emi(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. Transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('income','expense','emi','commission','rent','resale'),
    amount DECIMAL(15,2),
    description VARCHAR(255),
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 10. Rental Properties
CREATE TABLE IF NOT EXISTS rental_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_id INT NOT NULL,
    owner_id INT NOT NULL,
    tenant_id INT,
    rent_amount DECIMAL(15,2),
    rent_start DATE,
    rent_end DATE,
    status ENUM('active','vacant','terminated') DEFAULT 'active',
    agreement_doc VARCHAR(255),
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rent_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    due_date DATE,
    paid_date DATE,
    amount DECIMAL(15,2),
    status ENUM('pending','paid','overdue') DEFAULT 'pending',
    payment_mode VARCHAR(50),
    payment_gateway VARCHAR(50),
    payment_ref VARCHAR(100),
    FOREIGN KEY (rental_id) REFERENCES rental_properties(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. Resale Properties
CREATE TABLE IF NOT EXISTS resale_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_id INT NOT NULL,
    seller_id INT NOT NULL,
    buyer_id INT,
    resale_price DECIMAL(15,2),
    resale_date DATE,
    status ENUM('listed','sold','cancelled') DEFAULT 'listed',
    agreement_doc VARCHAR(255),
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resale_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resale_id INT NOT NULL,
    associate_id INT NOT NULL,
    commission_amount DECIMAL(15,2),
    paid_status ENUM('pending','paid') DEFAULT 'pending',
    paid_date DATE,
    FOREIGN KEY (resale_id) REFERENCES resale_properties(id) ON DELETE CASCADE,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. Commission Payouts
CREATE TABLE IF NOT EXISTS commission_payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    associate_id INT,
    level INT,
    commission_amount DECIMAL(15,2),
    payout_date DATE,
    status ENUM('pending','paid') DEFAULT 'pending',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 13. Leads
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    source VARCHAR(50),
    status ENUM('new','contacted','converted','lost') DEFAULT 'new',
    assigned_to INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES admin(aid) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 14. Audit Log
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(aid) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 15. AI Config & Logs
CREATE TABLE IF NOT EXISTS ai_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feature VARCHAR(100) NOT NULL,
    enabled TINYINT(1) DEFAULT 1,
    config_json TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admin(aid) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    input_text TEXT,
    ai_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 16. Miscellaneous
-- Add more tables as needed for future modules (e.g. notifications, file uploads, etc.)

-- End of APS Dream Homes Normalized Schema
