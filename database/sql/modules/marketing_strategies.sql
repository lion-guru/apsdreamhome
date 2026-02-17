CREATE TABLE IF NOT EXISTS marketing_strategies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  image_url VARCHAR(255),
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO marketing_strategies (title, description, image_url, active) VALUES
('Limited Time Offer', 'Get an extra 5% discount on all bookings made this week. Hurry up!', 'assets/marketing/offer1.jpg', 1),
('Free Site Visit', 'Book a free site visit for any property and get exclusive insights from our experts.', 'assets/marketing/offer2.jpg', 1),
('Referral Bonus', 'Refer a friend and earn ₹10,000 on their first booking.', 'assets/marketing/offer3.jpg', 1),
('Festive Bonanza', 'Special festive deals on select properties. Limited period only!', 'assets/marketing/offer4.jpg', 1);
