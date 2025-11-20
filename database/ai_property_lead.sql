-- AI Property Valuation (no new table, uses properties)
-- AI Lead Scoring
CREATE TABLE IF NOT EXISTS ai_lead_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  score INT NOT NULL,
  scored_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
