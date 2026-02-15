# APS Dream Home - Development Setup Guide

## Project Overview
APS Dream Home is a comprehensive real estate management system with CRM, property management, MLM, and advanced features. The project has multiple routing systems and requires careful setup for development.

## Quick Start Development Setup

### 1. Development Environment Configuration

Create `development_mode.php` in the root directory:

```php
<?php
// Development mode configuration
define('DEVELOPMENT_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Disable strict security for development
header('X-Frame-Options: SAMEORIGIN'); // Instead of DENY
header('X-Content-Type-Options: nosniff');
// Remove or modify CSP for development
// header("Content-Security-Policy: ...");

// Development database (use your local settings)
$dev_config = [
    'db_host' => 'localhost',
    'db_name' => 'apsdreamhome',
    'db_user' => 'root',
    'db_pass' => '',
    'base_url' => 'http://localhost/apsdreamhomefinal'
];

// Disable rate limiting in development
$_SESSION['skip_rate_limit'] = true;
?>
```

### 2. Database Setup

1. Create database:
```sql
CREATE DATABASE apsdreamhome;
```

2. Import the main database structure:
```bash
# Use the latest SQL file from database/ or sql/ directory
mysql -u root -p apsdreamhome < database/aps_dreamhome_complete.sql
```

3. Run setup scripts:
```bash
php setup_database.php
php add_demo_properties.php
```

### 3. Fix Admin Dashboard Access

Replace the strict security in `admin/dashboard.php`:

```php
<?php
// Add at the very top
require_once '../development_mode.php';

// Simplified session check for development
session_start();
if (!isset($_SESSION['admin_logged_in']) && !DEVELOPMENT_MODE) {
    header("Location: login.php");
    exit();
}

// Rest of your admin dashboard code
?>
```

### 4. Routing Issues Fix

Create a simple development router in `dev_router.php`:

```php
<?php
require_once 'development_mode.php';

// Simple routing for development
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/apsdreamhomefinal/';

// Remove base path from URI
$route = str_replace($base_path, '', $request_uri);
$route = strtok($route, '?');

// Simple route mapping
$routes = [
    '' => 'index.php',
    'admin' => 'admin/dashboard.php',
    'properties' => 'properties.php',
    'property/details' => 'property_details.php',
    'login' => 'login.php',
    'register' => 'registration.php'
];

if (isset($routes[$route])) {
    include $routes[$route];
} else {
    // Try to include the file directly
    $file_path = $route . '.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        include '404.php';
    }
}
?>
```

### 5. Session Management Fix

Create `includes/session_manager_dev.php`:

```php
<?php
// Development-friendly session manager
session_start();

// Simple session validation for development
function validate_session() {
    if (DEVELOPMENT_MODE) {
        return true; // Skip strict validation in development
    }
    
    // Production validation
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!validate_session()) {
        header("Location: login.php");
        exit();
    }
}
?>
```

### 6. Quick Development Login

Create `dev_login.php` for instant development access:

```php
<?php
require_once 'development_mode.php';
session_start();

if (DEVELOPMENT_MODE && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    
    // Quick login for development
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $username;
    $_SESSION['user_type'] = 'admin';
    $_SESSION['admin_logged_in'] = true;
    
    header("Location: admin/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Development Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .login-form { max-width: 300px; margin: 0 auto; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Development Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" value="admin" required>
            <button type="submit">Quick Login</button>
        </form>
    </div>
</body>
</html>
```

## Common Issues and Quick Fixes

### Issue 1: "Too many redirects" error
**Fix:** Clear browser cookies/cache and use `dev_login.php` instead of regular login

### Issue 2: "Access denied" on admin pages
**Fix:** Add to top of any admin file:
```php
<?php
require_once '../development_mode.php';
if (DEVELOPMENT_MODE) {
    session_start();
    if (!isset($_SESSION['admin_logged_in'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_type'] = 'admin';
    }
}
?>
```

### Issue 3: Database connection errors
**Fix:** Update `includes/config.php` with your local database credentials:
```php
<?php
$database_host = 'localhost';
$database_name = 'apsdreamhome';
$database_user = 'root';
$database_pass = '';
?>
```

### Issue 4: Routing not working
**Fix:** Use direct file paths or modify `.htaccess`:
```apache
# Development .htaccess
RewriteEngine On
RewriteBase /apsdreamhomefinal/

# Simple rewrite for development
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]
```

## Recommended Development Tools

1. **VS Code Extensions:**
   - PHP Intelephense
   - PHP Debug
   - HTML CSS Support
   - JavaScript (ES6) code snippets

2. **Browser Extensions:**
   - Web Developer
   - React Developer Tools (for modern UI)
   - Redux DevTools

3. **Database Tools:**
   - phpMyAdmin (included with XAMPP)
   - MySQL Workbench

## Next Steps After Setup

1. **Test basic functionality:**
   - Visit `http://localhost/apsdreamhomefinal/dev_login.php`
   - Login with any username
   - Navigate to admin dashboard

2. **Explore the system:**
   - Check property management features
   - Test CRM functionality
   - Review user management

3. **Start UI/UX improvements:**
   - Focus on `app/views/` directory for modern templates
   - Update CSS in `assets/css/`
   - Improve JavaScript functionality

## Troubleshooting Checklist

- [ ] Database imported successfully
- [ ] `development_mode.php` created
- [ ] Admin dashboard accessible
- [ ] No redirect loops
- [ ] Session working properly
- [ ] File permissions correct (755 for folders, 644 for files)

## Important Notes

- **Always backup** before making changes
- Use version control (Git) for tracking changes
- Test in multiple browsers
- Keep security in mind even in development
- Document your changes for team collaboration

## Support

If you encounter issues:
1. Check `error_log` files
2. Review browser console for JavaScript errors
3. Test database connections
4. Verify file permissions
5. Check Apache error logs

---

**Ready to start development!** Begin with the UI/UX improvements in the `app/views/` directory and work through the routing and security fixes outlined above.