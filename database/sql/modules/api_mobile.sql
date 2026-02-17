-- API Integrations
CREATE TABLE IF NOT EXISTS api_integrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(255) NOT NULL,
  api_url VARCHAR(255) NOT NULL,
  api_key VARCHAR(255),
  status VARCHAR(50) DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Mobile Devices
CREATE TABLE IF NOT EXISTS mobile_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  device_user VARCHAR(255) NOT NULL,
  push_token VARCHAR(255),
  platform VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
