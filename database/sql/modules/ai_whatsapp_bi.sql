-- AI Chatbot Config
CREATE TABLE IF NOT EXISTS ai_chatbot_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(50) NOT NULL,
  api_key VARCHAR(255),
  webhook_url VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- WhatsApp Automation Config
CREATE TABLE IF NOT EXISTS whatsapp_automation_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(50) NOT NULL,
  api_key VARCHAR(255),
  sender_number VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
