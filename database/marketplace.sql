-- Marketplace Apps/Integrations
CREATE TABLE IF NOT EXISTS marketplace_apps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_name VARCHAR(255) NOT NULL,
  provider VARCHAR(255),
  app_url VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
