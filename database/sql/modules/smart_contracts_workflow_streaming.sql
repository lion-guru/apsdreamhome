-- AI-Driven Smart Contracts
CREATE TABLE IF NOT EXISTS smart_contracts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agreement_name VARCHAR(255) NOT NULL,
  parties VARCHAR(255),
  terms TEXT,
  status VARCHAR(50) DEFAULT 'pending',
  blockchain_txn VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- External Workflow Automation (Zapier/Make)
CREATE TABLE IF NOT EXISTS workflow_automations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  provider VARCHAR(50),
  webhook_url VARCHAR(255),
  status VARCHAR(50) DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Enterprise Data Streaming (for Data Lake)
CREATE TABLE IF NOT EXISTS data_stream_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_type VARCHAR(100),
  payload JSON,
  streamed_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Cross-Channel Customer Journeys
CREATE TABLE IF NOT EXISTS customer_journeys (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  journey JSON,
  started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_touch_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
