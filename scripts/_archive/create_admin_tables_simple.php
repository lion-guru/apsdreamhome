<?php
/**
 * Create Missing Admin Tables - Simple MySQL Connection
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Checking and creating missing admin tables...\n\n";
    
    // Testimonials table
    $conn->query("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(255),
        customer_phone VARCHAR(50),
        rating INT DEFAULT 5,
        content TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        is_featured TINYINT(1) DEFAULT 0,
        property_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_featured (is_featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ testimonials table created/exists\n";
    
    // FAQs table
    $conn->query("CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question VARCHAR(500) NOT NULL,
        answer TEXT NOT NULL,
        category VARCHAR(100) DEFAULT 'General',
        sort_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_category (category),
        INDEX idx_sort (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ faqs table created/exists\n";
    
    // Knowledge Base table
    $conn->query("CREATE TABLE IF NOT EXISTS knowledge_base (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        category VARCHAR(100) DEFAULT 'Getting Started',
        status ENUM('draft', 'published') DEFAULT 'draft',
        views INT DEFAULT 0,
        author_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ knowledge_base table created/exists\n";
    
    // Blogs table
    $conn->query("CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255),
        content TEXT NOT NULL,
        category_id INT,
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        image VARCHAR(255),
        meta_title VARCHAR(255),
        meta_description TEXT,
        author_id INT,
        views INT DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_slug (slug),
        INDEX idx_category (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ blogs table created/exists\n";
    
    // Blog categories table
    $conn->query("CREATE TABLE IF NOT EXISTS blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ blog_categories table created/exists\n";
    
    // Insert default blog categories
    $result = $conn->query("SELECT COUNT(*) as count FROM blog_categories");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] == 0) {
        $categories = [
            ['Real Estate Tips', 'real-estate-tips', 'Tips for buying, selling, and investing in real estate'],
            ['Market News', 'market-news', 'Latest news and updates about the real estate market'],
            ['Property Guides', 'property-guides', 'Comprehensive guides for property buyers and sellers'],
            ['Company Updates', 'company-updates', 'News and updates about APS Dream Home'],
            ['Legal Advice', 'legal-advice', 'Legal information related to real estate transactions']
        ];
        
        foreach ($categories as $category) {
            $stmt = $conn->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute($category);
        }
        echo "✅ Default blog categories inserted\n";
    } else {
        echo "ℹ️ Blog categories already exist\n";
    }
    
    echo "\n🎉 All missing admin tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
