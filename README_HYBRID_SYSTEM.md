# APS Dream Home - Hybrid Template System

## Overview

This project implements a hybrid template system that combines:
1. **Enhanced Universal Template System** - For modern, dynamic pages
2. **Traditional Header/Footer Includes** - For legacy and admin pages
3. **Smart Routing Integration** - With web.php and router.php

## System Architecture

### File Structure
```
apsdreamhomefinal/
├── includes/
│   ├── hybrid_template_system.php  # Main hybrid system
│   ├── enhanced_universal_template.php  # Universal template
│   ├── header.php                 # Traditional header
│   ├── footer.php                 # Traditional footer
│   └── config.php                 # Configuration
├── router.php                     # Main router
├── .htaccess                      # URL rewriting
└── web.php                        # Laravel-style routes
```

### Hybrid Template Modes

1. **Universal Mode** (`universal`)
   - Modern, self-contained template system
   - Automatic navigation detection
   - SEO-friendly meta tags
   - Best for public pages

2. **Traditional Mode** (`traditional`)
   - Uses existing header.php and footer.php
   - Compatible with legacy code
   - Best for admin and authenticated pages

3. **Auto-Detect Mode** (`auto`)
   - Automatically chooses mode based on URL
   - API requests → API mode
   - Admin requests → Traditional mode
   - Public requests → Universal mode

## Installation & Setup

### 1. Update .htaccess
Ensure your `.htaccess` file has proper routing rules:
```apache
RewriteBase /apsdreamhomefinal/
RewriteRule ^(.*)$ router.php?url=$1 [QSA,L]
```

### 2. Include Hybrid System
Add to your main files:
```php
require_once 'includes/hybrid_template_system.php';
```

### 3. Configure Base URL
Set in `config.php`:
```php
define('BASE_URL', 'http://localhost/apsdreamhomefinal');
```

## Usage Examples

### Basic Page Rendering
```php
<?php
require_once 'includes/hybrid_template_system.php';

// Universal mode (default)
hybrid_template('universal')
    ->setTitle('Welcome to APS Dream Home')
    ->setDescription('Premium real estate properties')
    ->renderPage('<h1>Welcome!</h1><p>Your content here</p>');
```

### Traditional Mode
```php
<?php
require_once 'includes/hybrid_template_system.php';

// Traditional mode (uses header.php/footer.php)
hybrid_template('traditional')
    ->setTitle('Admin Dashboard')
    ->renderPage('<h1>Admin Content</h1>');
```

### Custom Menu Items
```php
<?php
hybrid_template()
    ->addMenuItem('custom', [
        'title' => 'Custom Page',
        'url' => '/custom',
        'icon' => 'fas fa-star'
    ])
    ->removeMenuItem('blog')
    ->renderPage('Content');
```

## Routing Integration

### web.php Routes
```php
// Public routes
Route::get('/', 'HomeController@index');
Route::get('/about', 'PageController@about');

// Admin routes  
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', 'AdminController@dashboard');
});
```

### router.php Integration
```php
// In router.php
require_once 'includes/hybrid_template_system.php';

// Auto-detect mode based on request
$mode = auto_detect_template_mode();
$GLOBALS['hybrid_template']->setMode($mode);
```

## Menu Configuration

### Default Menu Items
```php
[
    'home' => ['title' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
    'projects' => ['title' => 'Projects', 'url' => '/projects', 'icon' => 'fas fa-building'],
    'properties' => ['title' => 'Properties', 'url' => '/properties', 'icon' => 'fas fa-home'],
    'about' => ['title' => 'About Us', 'url' => '/about', 'icon' => 'fas fa-info-circle'],
    'contact' => ['title' => 'Contact', 'url' => '/contact', 'icon' => 'fas fa-phone'],
    'career' => ['title' => 'Careers', 'url' => '/career', 'icon' => 'fas fa-briefcase'],
    'blog' => ['title' => 'Blog', 'url' => '/blog', 'icon' => 'fas fa-blog']
]
```

### Authentication-Aware Menu
Menu items can be conditionally shown:
- `'auth' => true` - Only show when authenticated
- `'guest' => true` - Only show when not authenticated

## API Reference

### Core Methods

#### `hybrid_template(string $mode): HybridTemplateSystem`
Get template instance with specified mode.

#### `setTitle(string $title): self`
Set page title.

#### `setDescription(string $description): self`
Set meta description.

#### `addCSS(string $path): self`
Add CSS file.

#### `addJS(string $path, bool $defer = false, bool $async = false): self`
Add JavaScript file.

#### `renderPage(string $content, string $title = ''): void`
Render complete page.

#### `addMenuItem(string $key, array $item): self`
Add custom menu item.

#### `removeMenuItem(string $key): self`
Remove menu item.

### Helper Functions

#### `get_menu_items(): array`
Get all menu items.

#### `get_active_nav(): string`
Get active navigation key.

#### `auto_detect_template_mode(): string`
Auto-detect best template mode.

## Configuration

### Environment Detection
```php
// In config.php
define('ENVIRONMENT', 'development'); // or 'production'

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

### Database Configuration
```php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'apsdreamhome');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## Best Practices

### 1. Use Universal Mode for Public Pages
```php
// Best for SEO and modern design
hybrid_template('universal')->renderPage($content);
```

### 2. Use Traditional Mode for Admin Pages
```php
// Maintains compatibility with existing admin code
hybrid_template('traditional')->renderPage($admin_content);
```

### 3. Leverage Auto-Detection
```php
// Let the system choose the best mode
hybrid_template(auto_detect_template_mode())->renderPage($content);
```

### 4. Customize Menu Per Page
```php
// Different menus for different user roles
if ($user_role === 'admin') {
    hybrid_template()->addMenuItem('admin_dash', ['title' => 'Admin', 'url' => '/admin']);
}
```

## Troubleshooting

### Common Issues

1. **Pages not loading**
   - Check `.htaccess` rewrite rules
   - Verify `BASE_URL` in config.php

2. **Menu items not showing**
   - Check authentication status
   - Verify menu item configuration

3. **CSS/JS not loading**
   - Check file paths in `addCSS()`/`addJS()`
   - Verify file permissions

### Debug Mode
```php
// Enable debug mode
hybrid_template()
    ->addCustomJs('console.log("Debug mode enabled");')
    ->renderPage($content);
```

## Migration Guide

### From Old System

1. **Update includes**
   ```php
   // Old: include 'header.php';
   // New: hybrid_header();
   ```

2. **Update page rendering**
   ```php
   // Old: include template files manually
   // New: hybrid_template()->renderPage($content);
   ```

3. **Update menu handling**
   ```php
   // Old: Manual active state detection
   // New: Automatic active state detection
   ```

## Performance Optimization

### Caching Strategies
```php
// Enable template caching in production
if (ENVIRONMENT === 'production') {
    // Implement caching logic here
}
```

### Asset Optimization
```php
// Use CDN for common libraries
hybrid_template()
    ->addCSS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css')
    ->addJS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js');
```

## Security Considerations

### Input Sanitization
```php
// Always sanitize user input
$clean_input = htmlspecialchars($_POST['input']);
```

### Session Security
```php
// Secure session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
```

## Support

For issues and questions:
1. Check the `.htaccess` configuration
2. Verify file permissions
3. Check PHP error logs
4. Ensure all required includes are present

---

**Version**: 3.0.0  
**Last Updated**: 2024  
**Compatibility**: PHP 7.4+, Apache/Nginx