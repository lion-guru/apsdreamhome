<?php
/**
 * Create missing admin tables
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "=== Creating Missing Tables ===\n\n";

$tables = [
    'blog_posts' => "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT,
        excerpt VARCHAR(500),
        featured_image VARCHAR(500),
        category VARCHAR(100),
        tags VARCHAR(255),
        author_id INT UNSIGNED,
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        views INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'jobs' => "CREATE TABLE IF NOT EXISTS jobs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        requirements TEXT,
        location VARCHAR(200),
        job_type VARCHAR(50),
        salary_range VARCHAR(100),
        experience VARCHAR(100),
        status ENUM('active', 'closed', 'draft') DEFAULT 'active',
        posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'enquiries' => "CREATE TABLE IF NOT EXISTS enquiries (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        email VARCHAR(200),
        phone VARCHAR(20),
        subject VARCHAR(255),
        message TEXT,
        source VARCHAR(100),
        property_id INT UNSIGNED,
        assigned_to INT UNSIGNED,
        status ENUM('new', 'pending', 'responded', 'converted', 'closed') DEFAULT 'new',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'newsletter_subscribers' => "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        name VARCHAR(200),
        is_active TINYINT(1) DEFAULT 1,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        unsubscribed_at TIMESTAMP NULL,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables as $name => $sql) {
    try {
        $db->execute($sql);
        echo "✅ Created: $name\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⏭️  Already exists: $name\n";
        } else {
            echo "❌ Error creating $name: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Fixing testimonials table ===\n";
try {
    $cols = $db->fetchAll("DESCRIBE testimonials");
    $colNames = array_column($cols, 'Field');
    echo "Current columns: " . implode(', ', $colNames) . "\n";
    
    // Add missing columns
    if (!in_array('reviewed_by', $colNames)) {
        $db->execute("ALTER TABLE testimonials ADD COLUMN reviewed_by INT UNSIGNED");
        echo "✅ Added: reviewed_by\n";
    }
    if (!in_array('featured', $colNames)) {
        $db->execute("ALTER TABLE testimonials ADD COLUMN featured TINYINT(1) DEFAULT 0");
        echo "✅ Added: featured\n";
    }
    if (!in_array('submitted_at', $colNames)) {
        $db->execute("ALTER TABLE testimonials ADD COLUMN submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✅ Added: submitted_at\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Done!\n";