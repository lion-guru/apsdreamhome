-- API Usage Analytics
CREATE TABLE IF NOT EXISTS api_usage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dev_name VARCHAR(255),
  api_key VARCHAR(64),
  endpoint VARCHAR(255),
  usage_count INT DEFAULT 1,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- API Sandbox/Test Environments
CREATE TABLE IF NOT EXISTS api_sandbox (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dev_name VARCHAR(255),
  endpoint VARCHAR(255),
  payload TEXT,
  status VARCHAR(50) DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Partner Certification & Monetization
CREATE TABLE IF NOT EXISTS partner_certification (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partner_name VARCHAR(255),
  app_name VARCHAR(255),
  cert_status VARCHAR(50) DEFAULT 'pending',
  revenue_share INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
