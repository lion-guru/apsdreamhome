-- AI Legal/Document Review
CREATE TABLE IF NOT EXISTS legal_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  file_name VARCHAR(255) NOT NULL,
  file_url VARCHAR(255) NOT NULL,
  review_status VARCHAR(50) DEFAULT 'pending',
  ai_summary TEXT,
  ai_flags TEXT,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
