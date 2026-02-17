-- Developer Portal: API Developers
CREATE TABLE IF NOT EXISTS api_developers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dev_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  api_key VARCHAR(64) NOT NULL,
  status VARCHAR(50) DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
