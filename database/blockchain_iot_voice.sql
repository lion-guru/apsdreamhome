-- Blockchain Document Hash
ALTER TABLE customer_documents ADD COLUMN blockchain_hash VARCHAR(255) DEFAULT NULL;

-- IoT Device Management
CREATE TABLE IF NOT EXISTS iot_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  property_id INT NOT NULL,
  device_name VARCHAR(255),
  device_type VARCHAR(100),
  status VARCHAR(50) DEFAULT 'active',
  last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS iot_device_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NOT NULL,
  event_type VARCHAR(100),
  event_value VARCHAR(255),
  event_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Voice Assistant Config
CREATE TABLE IF NOT EXISTS voice_assistant_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(50) NOT NULL,
  api_key VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
