<?php
/**
 * Phase 51: Property Reviews Moderation + Testimonials System
 * Customer reviews + admin moderation + public display
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$db->exec("ALTER TABLE property_reviews ADD COLUMN IF NOT EXISTS helpful_count INT(11) NOT NULL DEFAULT 0");
$db->exec("ALTER TABLE property_reviews ADD COLUMN IF NOT EXISTS admin_response TEXT NULL");
$db->exec("ALTER TABLE property_reviews ADD COLUMN IF NOT EXISTS admin_response_at TIMESTAMP NULL");
$db->exec("ALTER TABLE property_reviews ADD INDEX IF NOT EXISTS idx_status_rating (status, rating)");
$db->exec("ALTER TABLE property_reviews ADD INDEX IF NOT EXISTS idx_helpful (helpful_count)");
echo "OK property_reviews enhanced\n";

$db->exec("ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS rating TINYINT(1) NULL DEFAULT 5");
$db->exec("ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS project_name VARCHAR(150) NULL");
$db->exec("ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS location VARCHAR(150) NULL");
$db->exec("ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL");
$db->exec("ALTER TABLE testimonials ADD INDEX IF NOT EXISTS idx_featured (is_featured, status)");
echo "OK testimonials enhanced\n";

$db->exec("DROP TABLE IF EXISTS review_helpful_votes");
$db->exec("CREATE TABLE review_helpful_votes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    review_id INT(11) NOT NULL,
    user_id BIGINT(20) UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_review (review_id),
    INDEX idx_user (user_id, review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK review_helpful_votes table created\n";

$db->exec("DROP TABLE IF EXISTS review_reports");
$db->exec("CREATE TABLE review_reports (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    review_id INT(11) NOT NULL,
    user_id BIGINT(20) UNSIGNED NULL,
    reason VARCHAR(100) NULL,
    description TEXT NULL,
    status ENUM('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
    reviewed_by BIGINT(20) UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_review (review_id),
    INDEX idx_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK review_reports table created\n";

$count = (int)$db->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
if ($count < 5) {
    $db->exec("INSERT INTO testimonials (customer_name, client_name, email, rating, content, testimonial, status, is_featured, project_name, location, approved_at) VALUES
    ('Rajesh Kumar', 'Rajesh Kumar', 'rajesh.k@example.com', 5, 'Excellent service from start to finish!', 'APS Dream Home helped us find our dream plot in Gorakhpur. The team was very professional and helped with all paperwork.', 'approved', 1, 'Suryoday Heights Phase 1', 'Gorakhpur', NOW()),
    ('Priya Sharma', 'Priya Sharma', 'priya.s@example.com', 5, 'Best real estate experience!', 'I was looking for a house in Lucknow and APS Dream Home made it so easy. Got possession within 6 months as promised.', 'approved', 1, 'Braj Radha Enclave', 'Lucknow', NOW()),
    ('Amit Verma', 'Amit Verma', 'amit.v@example.com', 4, 'Good experience overall', 'The property was as described. The team was responsive and helpful throughout the buying process. Only minor delays in documentation.', 'approved', 1, 'Raghunath City Center', 'Gorakhpur', NOW()),
    ('Sunita Devi', 'Sunita Devi', 'sunita.d@example.com', 5, 'Highly recommended for first-time buyers!', 'As a first-time buyer, I was nervous about the process. The APS team guided me through every step - from selection to registration. Very transparent.', 'approved', 0, 'Budh Bihar Colony', 'Kushinagar', NOW()),
    ('Mohit Singh', 'Mohit Singh', 'mohit.s@example.com', 5, 'Investment that paid off', 'Bought a plot 3 years ago through APS Dream Home. The value has doubled. Excellent location and proper documentation.', 'approved', 1, 'Suryoday Heights Phase 1', 'Gorakhpur', NOW())
    ON DUPLICATE KEY UPDATE customer_name=customer_name");
    echo "OK 5 sample testimonials inserted\n";
} else {
    echo "Testimonials table has $count records, skipping seed\n";
}

echo "DONE\n";?>