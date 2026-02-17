-- Feedback & Sentiment Analysis
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(255),
  feedback TEXT,
  sentiment VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
