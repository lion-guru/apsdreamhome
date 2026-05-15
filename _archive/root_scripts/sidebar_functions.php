
<?php
/**
 * Get role-based sidebar menu
 */
function getRoleBasedSidebar($userRole) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT menu_item, menu_url, menu_icon, menu_order, parent_menu 
        FROM user_role_permissions 
        WHERE user_role = ? AND is_active = 'yes' 
        ORDER BY menu_order
    ");
    
    $stmt->execute([$userRole]);
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize menu items into hierarchy
    $sidebar = [];
    $parentItems = [];
    
    foreach ($menuItems as $item) {
        if ($item['parent_menu'] === null) {
            $parentItems[] = $item;
        }
    }
    
    foreach ($parentItems as $parent) {
        $children = [];
        foreach ($menuItems as $item) {
            if ($item['parent_menu'] === $parent['menu_item']) {
                $children[] = $item;
            }
        }
        
        $sidebar[] = [
            'parent' => $parent,
            'children' => $children
        ];
    }
    
    return $sidebar;
}

/**
 * Render sidebar HTML
 */
function renderSidebar($userRole) {
    $sidebar = getRoleBasedSidebar($userRole);
    
    echo '<div class="sidebar">';
    echo '<div class="sidebar-header">';
    echo '<h3>' . ucfirst($userRole) . ' Panel</h3>';
    echo '</div>';
    echo '<ul class="sidebar-menu">';
    
    foreach ($sidebar as $item) {
        $parent = $item['parent'];
        
        echo '<li class="menu-item">';
        echo '<a href="' . $parent['menu_url'] . '" class="menu-link">';
        if ($parent['menu_icon']) {
            echo '<i class="' . $parent['menu_icon'] . '"></i>';
        }
        echo '<span>' . $parent['menu_item'] . '</span>';
        echo '</a>';
        
        if (!empty($item['children'])) {
            echo '<ul class="submenu">';
            foreach ($item['children'] as $child) {
                echo '<li class="submenu-item">';
                echo '<a href="' . $child['menu_url'] . '" class="submenu-link">';
                if ($child['menu_icon']) {
                    echo '<i class="' . $child['menu_icon'] . '"></i>';
                }
                echo '<span>' . $child['menu_item'] . '</span>';
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
    