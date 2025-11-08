<?php
/**
 * Hybrid Template System - APS Dream Home
 * 
 * This system combines the best of both worlds:
 * 1. Enhanced Universal Template System for modern pages
 * 2. Traditional header/footer includes for legacy pages
 * 3. Automatic routing integration with web.php
 * 4. Smart menu management with active state detection
 * 
 * @package APSDreamHome
 * @version 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH') && !isset($_SERVER['HTTP_HOST']) && php_sapi_name() !== 'cli') {
    exit('Direct access forbidden');
}

class HybridTemplateSystem {
    // Properties
    private string $mode = 'universal'; // 'universal' or 'traditional'
    private string $theme = 'default';
    private string $page_title = 'APS Dream Home';
    private string $meta_description = '';
    private array $meta_tags = [];
    private array $css_files = [];
    private array $js_files = [];
    private string $custom_css = '';
    private string $custom_js = '';
    private bool $show_navigation = true;
    private bool $show_footer = true;
    private array $menu_items = [];
    private string $active_nav = '';
    private array $breadcrumbs = [];

    /**
     * Constructor
     */
    public function __construct(string $mode = 'universal') {
        $this->mode = $mode;
        $this->initializeDefaultMenu();
        $this->detectActiveNavigation();
    }

    /**
     * Initialize default menu items
     */
    private function initializeDefaultMenu(): void {
        $this->menu_items = [
            'home' => ['title' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
            'projects' => ['title' => 'Projects', 'url' => '/projects', 'icon' => 'fas fa-building'],
            'properties' => ['title' => 'Properties', 'url' => '/properties', 'icon' => 'fas fa-home'],
            'about' => ['title' => 'About Us', 'url' => '/about', 'icon' => 'fas fa-info-circle'],
            'contact' => ['title' => 'Contact', 'url' => '/contact', 'icon' => 'fas fa-phone'],
            'career' => ['title' => 'Careers', 'url' => '/career', 'icon' => 'fas fa-briefcase'],
            'blog' => ['title' => 'Blog', 'url' => '/blog', 'icon' => 'fas fa-blog'],
            'dashboard' => ['title' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'auth' => true],
            'login' => ['title' => 'Login', 'url' => '/login', 'icon' => 'fas fa-sign-in-alt', 'guest' => true],
            'logout' => ['title' => 'Logout', 'url' => '/logout', 'icon' => 'fas fa-sign-out-alt', 'auth' => true]
        ];
    }

    /**
     * Detect active navigation based on current URL
     */
    private function detectActiveNavigation(): void {
        $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $current_path = trim($current_path, '/');
        
        foreach ($this->menu_items as $key => $item) {
            $item_path = trim(parse_url($item['url'], PHP_URL_PATH), '/');
            if ($current_path === $item_path || strpos($current_path, $item_path) === 0) {
                $this->active_nav = $key;
                break;
            }
        }

        // Fallback detection for complex URLs
        if (empty($this->active_nav)) {
            $path_parts = explode('/', $current_path);
            if (!empty($path_parts[0])) {
                $this->active_nav = $path_parts[0];
            }
        }
    }

    /**
     * Set template mode
     */
    public function setMode(string $mode): self {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Set page title
     */
    public function setTitle(string $title): self {
        $this->page_title = $title;
        return $this;
    }

    /**
     * Set page description
     */
    public function setDescription(string $description): self {
        $this->meta_description = $description;
        $this->addMeta('description', $description);
        return $this;
    }

    /**
     * Add meta tag
     */
    public function addMeta(string $name, string $content, string $type = 'name'): self {
        $this->meta_tags[$name] = ['content' => $content, 'type' => $type];
        return $this;
    }

    /**
     * Add CSS file
     */
    public function addCSS(string $path): self {
        $this->css_files[] = $path;
        return $this;
    }

    /**
     * Add JavaScript file
     */
    public function addJS(string $path, bool $defer = false, bool $async = false): self {
        $this->js_files[] = ['path' => $path, 'defer' => $defer, 'async' => $async];
        return $this;
    }

    /**
     * Add custom CSS
     */
    public function addCustomCss(string $css): self {
        $this->custom_css .= $css;
        return $this;
    }

    /**
     * Add custom JavaScript
     */
    public function addCustomJs(string $js): self {
        $this->custom_js .= $js;
        return $this;
    }

    /**
     * Add menu item
     */
    public function addMenuItem(string $key, array $item): self {
        $this->menu_items[$key] = $item;
        return $this;
    }

    /**
     * Remove menu item
     */
    public function removeMenuItem(string $key): self {
        unset($this->menu_items[$key]);
        return $this;
    }

    /**
     * Set active navigation
     */
    public function setActiveNav(string $nav): self {
        $this->active_nav = $nav;
        return $this;
    }

    /**
     * Render header
     */
    public function renderHeader(): void {
        if ($this->mode === 'universal') {
            $this->renderUniversalHeader();
        } else {
            $this->renderTraditionalHeader();
        }
    }

    /**
     * Render universal header
     */
    private function renderUniversalHeader(): void {
        ?><!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->page_title); ?></title>
            <?php $this->renderMetaTags(); ?>
            <?php $this->renderCSS(); ?>
            <?php if (!empty($this->custom_css)): ?>
                <style><?php echo $this->custom_css; ?></style>
            <?php endif; ?>
        </head>
        <body>
        <?php
        if ($this->show_navigation) {
            $this->renderNavigation();
        }
    }

    /**
     * Render traditional header (using existing header.php)
     */
    private function renderTraditionalHeader(): void {
        global $activeNav, $pageTitle;
        
        // Set globals for traditional header
        $activeNav = $this->active_nav;
        $pageTitle = $this->page_title;
        
        // Include the traditional header
        include __DIR__ . '/header.php';
    }

    /**
     * Render footer
     */
    public function renderFooter(): void {
        if ($this->mode === 'universal') {
            $this->renderUniversalFooter();
        } else {
            $this->renderTraditionalFooter();
        }
    }

    /**
     * Render universal footer
     */
    private function renderUniversalFooter(): void {
        if ($this->show_footer) {
            ?><footer class="bg-dark text-white py-4">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <p>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p>Powered by Hybrid Template System</p>
                        </div>
                    </div>
                </div>
            </footer><?php
        }
        
        $this->renderJavaScript();
        
        if (!empty($this->custom_js)) {
            echo '<script>' . $this->custom_js . '</script>';
        }
        
        ?></body></html><?php
    }

    /**
     * Render traditional footer (using existing footer.php)
     */
    private function renderTraditionalFooter(): void {
        include __DIR__ . '/footer.php';
    }

    /**
     * Render navigation menu
     */
    private function renderNavigation(): void {
        ?><nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="fas fa-home me-2"></i>
                    APS Dream Home
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto"><?php
                    foreach ($this->menu_items as $key => $item) {
                        // Check authentication requirements
                        $is_authenticated = isset($_SESSION['user_id']);
                        
                        if (isset($item['auth']) && $item['auth'] && !$is_authenticated) {
                            continue;
                        }
                        
                        if (isset($item['guest']) && $item['guest'] && $is_authenticated) {
                            continue;
                        }
                        
                        $is_active = $this->active_nav === $key;
                        ?><li class="nav-item">
                            <a class="nav-link<?php echo $is_active ? ' active' : ''; ?>" 
                               href="<?php echo htmlspecialchars($item['url']); ?>">
                                <?php if (!empty($item['icon'])): ?>
                                    <i class="<?php echo $item['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </li><?php
                    }
                    ?></ul>
                </div>
            </div>
        </nav><?php
    }

    /**
     * Render meta tags
     */
    private function renderMetaTags(): void {
        foreach ($this->meta_tags as $name => $meta) {
            echo sprintf(
                '<meta %s="%s" content="%s">' . PHP_EOL,
                $meta['type'],
                htmlspecialchars($name),
                htmlspecialchars($meta['content'])
            );
        }
    }

    /**
     * Render CSS files
     */
    private function renderCSS(): void {
        foreach ($this->css_files as $css) {
            echo sprintf(
                '<link rel="stylesheet" href="%s">' . PHP_EOL,
                htmlspecialchars($css)
            );
        }
    }

    /**
     * Render JavaScript files
     */
    private function renderJavaScript(): void {
        foreach ($this->js_files as $js) {
            $attrs = '';
            if ($js['defer']) $attrs .= ' defer';
            if ($js['async']) $attrs .= ' async';
            
            echo sprintf(
                '<script src="%s"%s></script>' . PHP_EOL,
                htmlspecialchars($js['path']),
                $attrs
            );
        }
    }

    /**
     * Render complete page
     */
    public function renderPage(string $content, string $title = '', string $layout = 'default'): void {
        if (!empty($title)) {
            $this->page_title = $title;
        }
        
        ob_start();
        $this->renderHeader();
        echo $content;
        $this->renderFooter();
        ob_end_flush();
    }

    /**
     * Get menu items for external use
     */
    public function getMenuItems(): array {
        return $this->menu_items;
    }

    /**
     * Get active navigation
     */
    public function getActiveNav(): string {
        return $this->active_nav;
    }
}

// Global instance
$GLOBALS['hybrid_template'] = new HybridTemplateSystem();

/**
 * Convenience functions
 */

function hybrid_template(string $mode = 'universal'): HybridTemplateSystem {
    global $hybrid_template;
    return $hybrid_template->setMode($mode);
}

function hybrid_page(string $content, string $title = 'APS Dream Home', string $mode = 'universal'): void {
    hybrid_template($mode)->renderPage($content, $title);
}

function hybrid_header(): void {
    hybrid_template()->renderHeader();
}

function hybrid_footer(): void {
    hybrid_template()->renderFooter();
}

function get_menu_items(): array {
    return hybrid_template()->getMenuItems();
}

function get_active_nav(): string {
    return hybrid_template()->getActiveNav();
}

// Auto-detect mode based on request
function auto_detect_template_mode(): string {
    // Check if it's an API request
    if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
        return 'api';
    }
    
    // Check if it's an admin request
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') === 0) {
        return 'traditional';
    }
    
    // Check if it's an auth request
    if (strpos($_SERVER['REQUEST_URI'], '/auth/') === 0) {
        return 'traditional';
    }
    
    // Default to universal for public pages
    return 'universal';
}

// Initialize with auto-detected mode
$GLOBALS['hybrid_template']->setMode(auto_detect_template_mode());