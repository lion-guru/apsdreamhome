<?php
/**
 * RBAC-Based Sidebar Menu
 * 
 * This file dynamically renders the admin sidebar menu based on user role
 * and custom permissions using the AdminMenuService.
 */

use App\Services\AdminMenuService;

// Get current page for active state
$currentPage = $active_page ?? basename($_SERVER['REQUEST_URI'] ?? '');

// Initialize menu service
$menuService = new AdminMenuService();
$menuItems = $menuService->getMenuItems();
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php foreach ($menuItems as $item): ?>
                <?php
                $hasChildren = !empty($item['children']);
                $isActive = $currentPage === ltrim($item['url'], '/') || 
                          ($hasChildren && strpos($currentPage, ltrim($item['url'], '/')) === 0);
                ?>
                
                <?php if ($hasChildren): ?>
                    <!-- Parent Menu Item with Submenu -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($item['url']); ?>" 
                           data-bs-toggle="collapse" 
                           data-bs-target="#submenu-<?php echo $item['id']; ?>" 
                           aria-expanded="<?php echo $isActive ? 'true' : 'false'; ?>">
                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i> 
                            <?php echo htmlspecialchars($item['name']); ?>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <ul class="collapse nav flex-column ms-3 <?php echo $isActive ? 'show' : ''; ?>" 
                            id="submenu-<?php echo $item['id']; ?>">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php
                                $isChildActive = $currentPage === ltrim($child['url'], '/');
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $isChildActive ? 'active' : ''; ?>" 
                                       href="<?php echo htmlspecialchars($child['url']); ?>">
                                        <i class="fas <?php echo htmlspecialchars($child['icon']); ?>"></i> 
                                        <?php echo htmlspecialchars($child['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Single Menu Item -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" 
                           href="<?php echo htmlspecialchars($item['url']); ?>">
                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i> 
                            <?php echo htmlspecialchars($item['name']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- View Site Link -->
            <li class="nav-item mt-3 pt-3 border-top">
                <a class="nav-link" href="<?php echo BASE_URL ?? '/'; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
            </li>
        </ul>
    </div>
</nav>
