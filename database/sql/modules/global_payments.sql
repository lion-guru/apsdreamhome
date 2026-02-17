-- Global Payments & In-App Purchases
CREATE TABLE IF NOT EXISTS global_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client VARCHAR(255),
  amount DECIMAL(12,2) DEFAULT 0.0,
  currency VARCHAR(10) DEFAULT 'INR',
  purpose VARCHAR(255),
  status VARCHAR(50) DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
