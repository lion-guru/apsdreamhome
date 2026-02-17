-- API Monetization Engine
CREATE TABLE IF NOT EXISTS api_monetization (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dev_name VARCHAR(255),
  endpoint VARCHAR(255),
  price DECIMAL(10,2) DEFAULT 0.0,
  status VARCHAR(50) DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
