-- Partner Rewards & Loyalty
CREATE TABLE IF NOT EXISTS partner_rewards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partner_email VARCHAR(255),
  points INT DEFAULT 0,
  description VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
