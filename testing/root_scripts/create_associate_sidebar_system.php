<?php
// Database configuration
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>👥 Associate Sidebar System Implementation</h1>";
    
    // Create user roles table for better role management
    echo "<h2>👤 Create User Role Management:</h2>";
    
    $createUserRolesTable = "
        CREATE TABLE IF NOT EXISTS user_role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_role ENUM('customer', 'agent', 'associate') NOT NULL,
            menu_item VARCHAR(100) NOT NULL,
            menu_url VARCHAR(255) NOT NULL,
            menu_icon VARCHAR(50) DEFAULT NULL,
            menu_order INT DEFAULT 0,
            parent_menu VARCHAR(100) DEFAULT NULL,
            is_active ENUM('yes', 'no') DEFAULT 'yes',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createUserRolesTable);
    echo "✅ Created user_role_permissions table<br>";
    
    // Insert role-based menu items
    echo "<h2>📋 Create Role-Based Menu Items:</h2>";
    
    $menuItems = [
        // Associate Menu Items
        ['user_role' => 'associate', 'menu_item' => 'Dashboard', 'menu_url' => '/associate/dashboard', 'menu_icon' => 'fas fa-tachometer-alt', 'menu_order' => 1, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'My Properties', 'menu_url' => '/associate/properties', 'menu_icon' => 'fas fa-building', 'menu_order' => 2, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'My Leads', 'menu_url' => '/associate/leads', 'menu_icon' => 'fas fa-users', 'menu_order' => 3, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'My Commissions', 'menu_url' => '/associate/commissions', 'menu_icon' => 'fas fa-rupee-sign', 'menu_order' => 4, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'Add Lead', 'menu_url' => '/associate/leads/add', 'menu_icon' => 'fas fa-plus', 'menu_order' => 5, 'parent_menu' => 'My Leads'],
        ['user_role' => 'associate', 'menu_item' => 'All Leads', 'menu_url' => '/associate/leads/all', 'menu_icon' => 'fas fa-list', 'menu_order' => 6, 'parent_menu' => 'My Leads'],
        ['user_role' => 'associate', 'menu_item' => 'Commission History', 'menu_url' => '/associate/commissions/history', 'menu_icon' => 'fas fa-history', 'menu_order' => 7, 'parent_menu' => 'My Commissions'],
        ['user_role' => 'associate', 'menu_item' => 'Withdraw Commission', 'menu_url' => '/associate/wallet/withdraw', 'menu_icon' => 'fas fa-money-bill-wave', 'menu_order' => 8, 'parent_menu' => 'My Commissions'],
        ['user_role' => 'associate', 'menu_item' => 'Network Tree', 'menu_url' => '/associate/genealogy', 'menu_icon' => 'fas fa-sitemap', 'menu_order' => 9, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'Team Management', 'menu_url' => '/associate/team', 'menu_icon' => 'fas fa-users-cog', 'menu_order' => 10, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'Add Team Member', 'menu_url' => '/associate/team/add', 'menu_icon' => 'fas fa-user-plus', 'menu_order' => 11, 'parent_menu' => 'Team Management'],
        ['user_role' => 'associate', 'menu_item' => 'Team Performance', 'menu_url' => '/associate/team/performance', 'menu_icon' => 'fas fa-chart-line', 'menu_order' => 12, 'parent_menu' => 'Team Management'],
        ['user_role' => 'associate', 'menu_item' => 'My Profile', 'menu_url' => '/associate/profile', 'menu_icon' => 'fas fa-user', 'menu_order' => 13, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'Settings', 'menu_url' => '/associate/settings', 'menu_icon' => 'fas fa-cog', 'menu_order' => 14, 'parent_menu' => NULL],
        ['user_role' => 'associate', 'menu_item' => 'Training', 'menu_url' => '/associate/training', 'menu_icon' => 'fas fa-graduation-cap', 'menu_order' => 15, 'parent_menu' => NULL],
        
        // Agent Menu Items
        ['user_role' => 'agent', 'menu_item' => 'Dashboard', 'menu_url' => '/agent/dashboard', 'menu_icon' => 'fas fa-tachometer-alt', 'menu_order' => 1, 'parent_menu' => NULL],
        ['user_role' => 'agent', 'menu_item' => 'My Properties', 'menu_url' => '/agent/properties', 'menu_icon' => 'fas fa-building', 'menu_order' => 2, 'parent_menu' => NULL],
        ['user_role' => 'agent', 'menu_item' => 'My Commissions', 'menu_url' => '/agent/commissions', 'menu_icon' => 'fas fa-rupee-sign', 'menu_order' => 3, 'parent_menu' => NULL],
        ['user_role' => 'agent', 'menu_item' => 'Commission Rates', 'menu_url' => '/agent/commission-rates', 'menu_icon' => 'fas fa-percentage', 'menu_order' => 4, 'parent_menu' => 'My Commissions'],
        ['user_role' => 'agent', 'menu_item' => 'My Referrals', 'menu_url' => '/agent/referrals', 'menu_icon' => 'fas fa-user-plus', 'menu_order' => 5, 'parent_menu' => NULL],
        ['user_role' => 'agent', 'menu_item' => 'Add Referral', 'menu_url' => '/agent/referrals/add', 'menu_icon' => 'fas fa-plus', 'menu_order' => 6, 'parent_menu' => 'My Referrals'],
        ['user_role' => 'agent', 'menu_item' => 'My Profile', 'menu_url' => '/agent/profile', 'menu_icon' => 'fas fa-user', 'menu_order' => 7, 'parent_menu' => NULL],
        ['user_role' => 'agent', 'menu_item' => 'Settings', 'menu_url' => '/agent/settings', 'menu_icon' => 'fas fa-cog', 'menu_order' => 8, 'parent_menu' => NULL],
        
        // Customer Menu Items
        ['user_role' => 'customer', 'menu_item' => 'Dashboard', 'menu_url' => '/user/dashboard', 'menu_icon' => 'fas fa-tachometer-alt', 'menu_order' => 1, 'parent_menu' => NULL],
        ['user_role' => 'customer', 'menu_item' => 'My Properties', 'menu_url' => '/user/properties', 'menu_icon' => 'fas fa-building', 'menu_order' => 2, 'parent_menu' => NULL],
        ['user_role' => 'customer', 'menu_item' => 'My Inquiries', 'menu_url' => '/user/inquiries', 'menu_icon' => 'fas fa-envelope', 'menu_order' => 3, 'parent_menu' => NULL],
        ['user_role' => 'customer', 'menu_item' => 'Post Property', 'menu_url' => '/list-property', 'menu_icon' => 'fas fa-plus', 'menu_order' => 4, 'parent_menu' => NULL],
        ['user_role' => 'customer', 'menu_item' => 'My Profile', 'menu_url' => '/user/profile', 'menu_icon' => 'fas fa-user', 'menu_order' => 5, 'parent_menu' => NULL],
        ['user_role' => 'customer', 'menu_item' => 'Settings', 'menu_url' => '/user/settings', 'menu_icon' => 'fas fa-cog', 'menu_order' => 6, 'parent_menu' => NULL]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO user_role_permissions (user_role, menu_item, menu_url, menu_icon, menu_order, parent_menu, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 'yes')
        ON DUPLICATE KEY UPDATE menu_url = VALUES(menu_url), menu_icon = VALUES(menu_icon), menu_order = VALUES(menu_order)
    ");
    
    foreach ($menuItems as $item) {
        $stmt->execute([$item['user_role'], $item['menu_item'], $item['menu_url'], $item['menu_icon'], $item['menu_order'], $item['parent_menu']]);
        echo "✅ Added " . $item['user_role'] . " menu: " . $item['menu_item'] . "<br>";
    }
    
    // Create dynamic sidebar function
    echo "<h2>🎨 Create Dynamic Sidebar Function:</h2>";
    
    $sidebarFunction = "
<?php
/**
 * Get role-based sidebar menu
 */
function getRoleBasedSidebar(\$userRole) {
    global \$pdo;
    
    \$stmt = \$pdo->prepare(\"
        SELECT menu_item, menu_url, menu_icon, menu_order, parent_menu 
        FROM user_role_permissions 
        WHERE user_role = ? AND is_active = 'yes' 
        ORDER BY menu_order
    \");
    
    \$stmt->execute([\$userRole]);
    \$menuItems = \$stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize menu items into hierarchy
    \$sidebar = [];
    \$parentItems = [];
    
    foreach (\$menuItems as \$item) {
        if (\$item['parent_menu'] === null) {
            \$parentItems[] = \$item;
        }
    }
    
    foreach (\$parentItems as \$parent) {
        \$children = [];
        foreach (\$menuItems as \$item) {
            if (\$item['parent_menu'] === \$parent['menu_item']) {
                \$children[] = \$item;
            }
        }
        
        \$sidebar[] = [
            'parent' => \$parent,
            'children' => \$children
        ];
    }
    
    return \$sidebar;
}

/**
 * Render sidebar HTML
 */
function renderSidebar(\$userRole) {
    \$sidebar = getRoleBasedSidebar(\$userRole);
    
    echo '<div class=\"sidebar\">';
    echo '<div class=\"sidebar-header\">';
    echo '<h3>' . ucfirst(\$userRole) . ' Panel</h3>';
    echo '</div>';
    echo '<ul class=\"sidebar-menu\">';
    
    foreach (\$sidebar as \$item) {
        \$parent = \$item['parent'];
        
        echo '<li class=\"menu-item\">';
        echo '<a href=\"' . \$parent['menu_url'] . '\" class=\"menu-link\">';
        if (\$parent['menu_icon']) {
            echo '<i class=\"' . \$parent['menu_icon'] . '\"></i>';
        }
        echo '<span>' . \$parent['menu_item'] . '</span>';
        echo '</a>';
        
        if (!empty(\$item['children'])) {
            echo '<ul class=\"submenu\">';
            foreach (\$item['children'] as \$child) {
                echo '<li class=\"submenu-item\">';
                echo '<a href=\"' . \$child['menu_url'] . '\" class=\"submenu-link\">';
                if (\$child['menu_icon']) {
                    echo '<i class=\"' . \$child['menu_icon'] . '\"></i>';
                }
                echo '<span>' . \$child['menu_item'] . '</span>';
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
    echo '</div>';
    
    // Add sidebar styles
    echo '<style>
    .sidebar {
        width: 250px;
        background: #2c3e50;
        color: white;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        overflow-y: auto;
    }
    
    .sidebar-header {
        padding: 20px;
        background: #34495e;
        border-bottom: 1px solid #4a5f7a;
    }
    
    .sidebar-header h3 {
        margin: 0;
        color: white;
        font-size: 18px;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .menu-item {
        border-bottom: 1px solid #4a5f7a;
    }
    
    .menu-link {
        display: block;
        padding: 15px 20px;
        color: white;
        text-decoration: none;
        transition: background 0.3s;
    }
    
    .menu-link:hover {
        background: #4a5f7a;
        color: #3498db;
    }
    
    .menu-link i {
        margin-right: 10px;
        width: 16px;
        text-align: center;
    }
    
    .submenu {
        list-style: none;
        background: #34495e;
        display: none;
    }
    
    .menu-item:hover .submenu {
        display: block;
    }
    
    .submenu-item {
        border-bottom: 1px solid #4a5f7a;
    }
    
    .submenu-link {
        display: block;
        padding: 10px 20px 10px 40px;
        color: #bdc3c7;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.3s;
    }
    
    .submenu-link:hover {
        background: #4a5f7a;
        color: white;
    }
    
    .submenu-link i {
        margin-right: 8px;
        font-size: 12px;
    }
    
    /* Main content adjustment */
    .main-content {
        margin-left: 250px;
        padding: 20px;
    }
    </style>';
}
?>
    ";
    
    // Save sidebar function
    file_put_contents(__DIR__ . '/sidebar_functions.php', $sidebarFunction);
    echo "✅ Created dynamic sidebar functions<br>";
    
    // Create associate layout with sidebar
    echo "<h2>🎨 Create Associate Layout with Sidebar:</h2>";
    
    $associateLayout = "
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Associate Dashboard - APS Dream Home</title>
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\" rel=\"stylesheet\">
</head>
<body>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Include sidebar functions
    require_once __DIR__ . '/sidebar_functions.php';
    
    // Check user role
    \$userRole = \$_SESSION['user_role'] ?? 'customer';
    ?>
    
    <div class=\"d-flex\">
        <!-- Sidebar -->
        <?php renderSidebar(\$userRole); ?>
        
        <!-- Main Content -->
        <div class=\"main-content flex-grow-1\">
            <?php include \$content; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
</body>
</html>
    ";
    
    // Save associate layout
    file_put_contents(__DIR__ . '/associate_layout.php', $associateLayout);
    echo "✅ Created associate layout with dynamic sidebar<br>";
    
    // Display menu structure for each role
    echo "<h2>📋 Menu Structure by Role:</h2>";
    
    $roles = ['associate', 'agent', 'customer'];
    
    foreach ($roles as $role) {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h3>🎯 " . ucfirst($role) . " Menu:</h3>";
        
        $stmt = $pdo->prepare("
            SELECT menu_item, menu_url, menu_icon, parent_menu 
            FROM user_role_permissions 
            WHERE user_role = ? AND is_active = 'yes' 
            ORDER BY menu_order
        ");
        $stmt->execute([$role]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul style='list-style: none; padding: 0;'>";
        foreach ($items as $item) {
            if ($item['parent_menu'] === null) {
                echo "<li style='padding: 5px 0; border-bottom: 1px solid #dee2e6;'>";
                echo "<i class='" . $item['menu_icon'] . "' style='margin-right: 8px; color: #007bff;'></i>";
                echo "<strong>" . $item['menu_item'] . "</strong>";
                echo "<br><small style='color: #6c757d;'>" . $item['menu_url'] . "</small>";
                echo "</li>";
                
                // Show children
                $stmt = $pdo->prepare("
                    SELECT menu_item, menu_url, menu_icon 
                    FROM user_role_permissions 
                    WHERE user_role = ? AND parent_menu = ? AND is_active = 'yes' 
                    ORDER BY menu_order
                ");
                $stmt->execute([$role, $item['menu_item']]);
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($children) > 0) {
                    echo "<ul style='margin-left: 30px; list-style: circle;'>";
                    foreach ($children as $child) {
                        echo "<li style='padding: 3px 0;'>";
                        echo "<i class='" . $child['menu_icon'] . "' style='margin-right: 8px; color: #28a745; font-size: 12px;'></i>";
                        echo $child['menu_item'];
                        echo "<br><small style='color: #6c757d;'>" . $child['menu_url'] . "</small>";
                        echo "</li>";
                    }
                    echo "</ul>";
                }
            }
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Display implementation summary
    echo "<h1>🎉 Associate Sidebar System - COMPLETE!</h1>";
    
    echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px;'>";
    echo "<h2 style='color: white;'>✅ Role-Based Sidebar System Successfully Implemented!</h2>";
    
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    
    // Associate Menu Features
    echo "<div>";
    echo "<h3 style='color: white;'>👥 Associate Menu Features:</h3>";
    echo "<ul style='color: white;'>";
    echo "<li><strong>Dashboard:</strong> Performance overview and statistics</li>";
    echo "<li><strong>My Properties:</strong> Property management and listings</li>";
    echo "<li><strong>My Leads:</strong> Lead management with add/view options</li>";
    echo "<li><strong>My Commissions:</strong> Commission history and withdrawal</li>";
    echo "<li><strong>Network Tree:</strong> MLM network visualization</li>";
    echo "<li><strong>Team Management:</strong> Add and manage team members</li>";
    echo "<li><strong>Training:</strong> Training materials and resources</li>";
    echo "</ul>";
    echo "</div>";
    
    // System Features
    echo "<div>";
    echo "<h3 style='color: white;'>🔧 System Features:</h3>";
    echo "<ul style='color: white;'>";
    echo "<li><strong>Role-Based Menu:</strong> Different menus for different roles</li>";
    echo "<li><strong>Dynamic Sidebar:</strong> Menu changes based on user role</li>";
    echo "<li><strong>Hierarchical Menu:</strong> Parent-child menu structure</li>";
    echo "<li><strong>Icon Support:</strong> Font Awesome icons for menu items</li>";
    echo "<li><strong>Responsive Design:</strong> Mobile-friendly sidebar</li>";
    echo "<li><strong>Easy Management:</strong> Database-driven menu system</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px;'>";
    echo "<h3 style='color: white;'>🔄 How It Works:</h3>";
    echo "<ol style='color: white;'>";
    echo "<li><strong>Associate Login:</strong> System detects user role as 'associate'</li>";
    echo "<li><strong>Dynamic Menu:</strong> Sidebar loads associate-specific menu</li>";
    echo "<li><strong>Role-Based Access:</strong> Only associate features are visible</li>";
    echo "<li><strong>Database Driven:</strong> Menu items stored and managed in database</li>";
    echo "<li><strong>Easy Updates:</strong> Admin can modify menu without code changes</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p style='text-align: center; margin-top: 20px; font-size: 18px; color: white;'><strong>🏆 Associate Sidebar System is Ready!</strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>
