-- White-Label SaaS Instances
CREATE TABLE IF NOT EXISTS saas_instances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_name VARCHAR(255) NOT NULL,
  domain VARCHAR(255) NOT NULL,
  status VARCHAR(50) DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Optionally, extend process tracking for analytics
-- Add more fields as needed for advanced process analytics
