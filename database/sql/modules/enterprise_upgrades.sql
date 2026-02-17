-- Marketing Campaigns
CREATE TABLE IF NOT EXISTS marketing_campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  type ENUM('email','sms') NOT NULL,
  message TEXT NOT NULL,
  scheduled_at DATETIME DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'scheduled',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Customer Portal
CREATE TABLE IF NOT EXISTS customer_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  doc_name VARCHAR(255),
  status VARCHAR(50) DEFAULT 'uploaded',
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  paid_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- AI/Fraud/Document
-- (No new tables needed, uses existing bookings, payments, customer_documents)

-- Payment Gateway Config
CREATE TABLE IF NOT EXISTS payment_gateway_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(50) NOT NULL,
  api_key VARCHAR(255),
  api_secret VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Third-Party Integrations
CREATE TABLE IF NOT EXISTS third_party_integrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  api_token VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Role Change Approvals
CREATE TABLE IF NOT EXISTS role_change_approvals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  action ENUM('assign','remove') NOT NULL,
  requested_by INT NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  decided_by INT DEFAULT NULL,
  decided_at DATETIME DEFAULT NULL
);

-- Feedback & Support Tickets
CREATE TABLE IF NOT EXISTS feedback_tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  status ENUM('open','closed') DEFAULT 'open',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
