<?php
/**
 * Add New Admin Menu Items
 * Adds menu items for newly created admin features to admin_menu_items table
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
    
    echo "ðŸ”� Checking admin_menu_items table structure...\n";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'admin_menu_items'");
    if (!$result->fetch()) {
        // Create table if it doesn't exist
        $conn->query("CREATE TABLE admin_menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            url VARCHAR(255) NOT NULL,
            icon VARCHAR(50),
            section VARCHAR(50),
            parent_id INT NULL,
            order_index INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_section (section),
            INDEX idx_parent (parent_id),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "âœ… Created admin_menu_items table\n";
    } else {
        echo "âœ… admin_menu_items table exists\n";
    }
    
    echo "\nðŸ“� Adding new menu items...\n";
    
    // Clear existing menu items (optional - comment out if you want to keep existing)
    // $conn->query("TRUNCATE TABLE admin_menu_items");
    // echo "ðŸ—‘ï¸� Cleared existing menu items\n";
    
    // Check if we already have menu items
    $result = $conn->query("SELECT COUNT(*) as count FROM admin_menu_items");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $hasExisting = $row['count'] > 0;
    
    if ($hasExisting) {
        echo "â„¹ï¸� Menu items already exist. Checking if new ones need to be added...\n";
    }
    
    // Define new menu items
    $menuItems = [
        // Reports Section
        [
            'name' => 'Reports Dashboard',
            'url' => '/admin/reports-new',
            'icon' => 'fas fa-chart-bar',
            'section' => 'reports',
            'parent_id' => null,
            'order_index' => 100
        ],
        [
            'name' => 'Daily Reports',
            'url' => '/admin/reports-new/daily',
            'icon' => 'fas fa-calendar-day',
            'section' => 'reports',
            'parent_id' => null,
            'order_index' => 101
        ],
        [
            'name' => 'Weekly Reports',
            'url' => '/admin/reports-new/weekly',
            'icon' => 'fas fa-calendar-week',
            'section' => 'reports',
            'parent_id' => null,
            'order_index' => 102
        ],
        [
            'name' => 'Monthly Reports',
            'url' => '/admin/reports-new/monthly',
            'icon' => 'fas fa-calendar-alt',
            'section' => 'reports',
            'parent_id' => null,
            'order_index' => 103
        ],
        
        // Content Section
        [
            'name' => 'Blogs',
            'url' => '/admin/blogs',
            'icon' => 'fas fa-blog',
            'section' => 'content',
            'parent_id' => null,
            'order_index' => 200
        ],
        [
            'name' => 'Testimonials',
            'url' => '/admin/testimonials-new',
            'icon' => 'fas fa-quote-right',
            'section' => 'content',
            'parent_id' => null,
            'order_index' => 201
        ],
        [
            'name' => 'FAQs',
            'url' => '/admin/faqs-new',
            'icon' => 'fas fa-question-circle',
            'section' => 'content',
            'parent_id' => null,
            'order_index' => 202
        ],
        [
            'name' => 'Knowledge Base',
            'url' => '/admin/knowledge-base-new',
            'icon' => 'fas fa-book',
            'section' => 'content',
            'parent_id' => null,
            'order_index' => 203
        ]
    ];
    
    $addedCount = 0;
    $skippedCount = 0;
    
    foreach ($menuItems as $item) {
        // Check if item already exists
        $check = $conn->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
        $check->execute([$item['url']]);
        
        if (!$check->fetch()) {
            // Insert new item
            $stmt = $conn->prepare("INSERT INTO admin_menu_items 
                (name, url, icon, section, parent_id, order_index, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $item['name'],
                $item['url'],
                $item['icon'],
                $item['section'],
                $item['parent_id'],
                $item['order_index']
            ]);
            echo "âœ… Added: {$item['name']} ({$item['url']})\n";
            $addedCount++;
        } else {
            echo "â�­ï¸� Skipped (already exists): {$item['name']}\n";
            $skippedCount++;
        }
    }
    
    echo "\nðŸ“Š Summary:\n";
    echo "âœ… Added: $addedCount menu items\n";
    echo "â�­ï¸� Skipped: $skippedCount existing items\n";
    
    // Show total count
    $result = $conn->query("SELECT COUNT(*) as total FROM admin_menu_items");
    $total = $result->fetch(PDO::FETCH_ASSOC)['total'];
    echo "ðŸ“ˆ Total menu items in database: $total\n";
    
    echo "\nðŸŽ‰ Menu items update complete!\n";
    echo "\nðŸ“� Next: Clear admin menu cache and reload admin panel to see new menu items\n";
    
} catch (PDOException $e) {
    echo "â�Œ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "â�Œ Error: " . $e->getMessage() . "\n";
}?>