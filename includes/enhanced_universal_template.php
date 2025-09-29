<?php
/**
 * Enhanced Universal Template System - Fixed Version
 * 
 * A comprehensive template management system with all necessary methods
 */

// Prevent direct access - but allow when accessed via web server
if (!defined('ABSPATH') && !isset($_SERVER['HTTP_HOST'])) {
    exit('Direct access forbidden');
}

class EnhancedUniversalTemplate {
    // Properties
    private string $theme = 'default';
    private string $layout = 'full';
    private string $page_title = 'APS Dream Home';
    private string $meta_description = '';
    private array $meta_tags = [];
    private string $custom_css = '';
    private string $custom_js = '';
    private array $css_files = [];
    private array $js_files = [];
    private bool $show_navigation = true;
    private bool $show_footer = true;
    private bool $enable_seo = true;
    private bool $enable_social = true;
    private bool $enable_security = true;

    /**
     * Set the current theme
     */
    public function setTheme(string $theme): self {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Set the page title
     */
    public function setTitle(string $title): self {
        $this->page_title = $title;
        return $this;
    }
    
    /**
     * Set the page description
     */
    public function setDescription(string $description): self {
        $this->meta_description = $description;
        $this->addMeta('description', $description);
        return $this;
    }
    
    /**
     * Add a CSS file to the page
     */
    public function addCSS(string $path): self {
        $this->css_files[] = $path;
        return $this;
    }
    
    /**
     * Add a JavaScript file to the page
     */
    public function addJS(string $path, bool $defer = false, bool $async = false): self {
        $this->js_files[] = [
            'path' => $path,
            'defer' => $defer,
            'async' => $async
        ];
        return $this;
    }

    /**
     * Add a meta tag to the page
     */
    public function addMeta(string $name, string $content, string $type = 'name'): self {
        $this->meta_tags[$name] = [
            'content' => $content,
            'type' => $type
        ];
        return $this;
    }

    /**
     * Add a CSS file to include
     */
    public function addCssFile(string $path): self {
        $this->css_files[] = $path;
        return $this;
    }

    /**
     * Add a JavaScript file to include
     */
    public function addJsFile(string $path): self {
        $this->js_files[] = $path;
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
     * Render a complete page with the given content and title
     */
    public function renderPage(string $content, string $title, string $layout = 'default'): void {
        $this->page_title = $title;
        $this->layout = $layout;
        
        // Start output buffering
        ob_start();
        
        // Include the header
        $this->renderHeader();
        
        // Output the main content
        echo $content;
        
        // Include the footer
        $this->renderFooter();
        
        // Flush the output buffer
        ob_end_flush();
    }
    
    /**
     * Render the header
     */
    private function renderHeader(): void {
        // Set default meta tags if not already set
        if (empty($this->meta_tags)) {
            $this->addMeta('description', 'APS Dream Home - Your trusted real estate partner');
            $this->addMeta('viewport', 'width=device-width, initial-scale=1.0');
            $this->addMeta('robots', 'index, follow');
        }
        
        // Include the header template if it exists, otherwise use a fallback
        $header_file = __DIR__ . '/templates/header.php';
        if (file_exists($header_file)) {
            include $header_file;
        } else {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php echo htmlspecialchars($this->page_title); ?></title>
                <?php 
                // Output meta tags
                foreach ($this->meta_tags as $name => $meta) {
                    echo sprintf(
                        '<meta %s="%s" content="%s">' . PHP_EOL,
                        $meta['type'],
                        htmlspecialchars($name),
                        htmlspecialchars($meta['content'])
                    );
                }
                
                // Output CSS files
                foreach ($this->css_files as $css) {
                    echo sprintf(
                        '<link rel="stylesheet" href="%s">' . PHP_EOL,
                        htmlspecialchars($css)
                    );
                }
                
                // Output custom CSS
                if (!empty($this->custom_css)) {
                    echo '<style>' . $this->custom_css . '</style>' . PHP_EOL;
                }
                ?>
            </head>
            <body>
            <?php
        }
    }
    
    /**
     * Render the footer
     */
    private function renderFooter(): void {
        $footer_file = __DIR__ . '/templates/footer.php';
        if (file_exists($footer_file)) {
            include $footer_file;
        } else {
            // Fallback footer
            ?>
                <footer>
                    <p>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                </footer>
                
                <?php
                // Output JavaScript files
                foreach ($this->js_files as $js) {
                    echo sprintf(
                        '<script src="%s"></script>' . PHP_EOL,
                        htmlspecialchars($js)
                    );
                }
                
                // Output custom JavaScript
                if (!empty($this->custom_js)) {
                    echo '<script>' . $this->custom_js . '</script>' . PHP_EOL;
                }
                ?>
            </body>
            </html>
            <?php
        }
    }
}

// Create a global instance
$GLOBALS['aps_template'] = new EnhancedUniversalTemplate();

/**
 * Convenience Functions
 */

/**
 * Get the template instance with optional theme
 */
function template(string $theme = 'default'): EnhancedUniversalTemplate {
    global $aps_template;
    return $aps_template->setTheme($theme);
}

/**
 * Render a standard page
 */
function page(string $content, string $title = 'APS Dream Home', string $theme = 'default'): void {
    template($theme)->renderPage($content, $title);
}

/**
 * Render a dashboard page
 */
function dashboard_page(string $content, string $title = 'Dashboard'): void {
    template('dashboard')->renderPage($content, $title, 'dashboard');
}

/**
 * Render a login page
 */
function login_page(string $content, string $title = 'Login'): void {
    template('login')->renderPage($content, $title, 'login');
}

/**
 * Render an admin page
 */
function admin_page(string $content, string $title = 'Admin Panel'): void {
    template('admin')->renderPage($content, $title, 'admin');
}
