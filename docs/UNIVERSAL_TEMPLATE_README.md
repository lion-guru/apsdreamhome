# 🎯 **Universal Template System - Complete Guide**

## Overview
The Universal Template System combines the best of all your existing designs into one flexible, powerful system that can be used everywhere in your APS Dream Home project.

## ✨ **Key Features**

- 🎨 **4 Built-in Themes**: Default, Dashboard, Login, Admin
- 🔧 **Flexible Components**: Cards, Alerts, Buttons, Navigation
- 📱 **Responsive Design**: Works on all devices
- 🎭 **Theme Switching**: Change themes per page
- 🛠️ **Customizable**: Add custom CSS/JS easily
- 🚀 **Performance Optimized**: Fast loading and rendering

## 📁 **File Structure**

```
your-project/
├── includes/
│   └── universal_template.php    # 🎯 Main template system
├── clean_login.php              # Login page example
├── clean_dashboard.php          # Dashboard page example
├── universal_template_examples.php # More examples
└── your-pages.php               # Your pages
```

## 🚀 **Quick Start**

### **1. Basic Page (Default Theme)**
```php
<?php
require_once __DIR__ . '/includes/universal_template.php';

$content = "
<div class='container py-5'>
    <h1>My Page</h1>
    <p>Content here!</p>
</div>";

page($content, 'Page Title');
?>
```

### **2. Dashboard Page (Dashboard Theme)**
```php
<?php
require_once __DIR__ . '/includes/universal_template.php';

$content = "
<div class='dashboard-container'>
    <h1>Dashboard Content</h1>
</div>";

dashboard_page($content, 'Dashboard');
?>
```

### **3. Login Page (Login Theme)**
```php
<?php
require_once __DIR__ . '/includes/universal_template.php';

$content = "
<div class='login-container'>
    <h1>Login Form</h1>
</div>";

login_page($content, 'Login');
?>
```

### **4. Admin Page (Admin Theme)**
```php
<?php
require_once __DIR__ . '/includes/universal_template.php';

$content = "
<div class='admin-container'>
    <h1>Admin Panel</h1>
</div>";

admin_page($content, 'Admin Panel');
?>
```

## 🎨 **Available Themes**

### **1. Default Theme**
- Primary: `#4e73df` (Blue)
- Background: Gradient blue-purple
- Best for: Homepage, Landing pages, General content

### **2. Dashboard Theme**
- Primary: `#4e73df` (Blue)
- Background: Light gradient
- Features: Floating elements, glass morphism cards
- Best for: User dashboards, Stats pages

### **3. Login Theme**
- Primary: `#28a745` (Green)
- Background: Gradient blue-purple
- Features: Centered login container, custom styling
- Best for: Login, Registration, Auth pages

### **4. Admin Theme**
- Primary: `#6f42c1` (Purple)
- Background: Dark gradient
- Features: Admin sidebar, professional styling
- Best for: Admin panels, Management pages

## 🧩 **Built-in Components**

### **Cards**
```php
// Simple card
<div class='card'>
    <div class='card-body'>
        <h5>Card Title</h5>
        <p>Card content here</p>
    </div>
</div>

// Centered card
<div class='card text-center'>
    <div class='card-body'>
        <i class='fas fa-home fa-3x text-primary mb-3'></i>
        <h5>Feature Title</h5>
        <p>Description here</p>
    </div>
</div>
```

### **Alerts**
```php
// Success alert
<div class='alert alert-success'>
    <i class='fas fa-check-circle me-2'></i>
    Success message!
</div>

// Error alert
<div class='alert alert-danger'>
    <i class='fas fa-exclamation-triangle me-2'></i>
    Error message!
</div>

// Warning alert
<div class='alert alert-warning'>
    <i class='fas fa-exclamation-circle me-2'></i>
    Warning message!
</div>

// Info alert
<div class='alert alert-info'>
    <i class='fas fa-info-circle me-2'></i>
    Info message!
</div>
```

### **Buttons**
```php
// Primary button
<a href='page.php' class='btn btn-primary'>
    <i class='fas fa-search me-2'></i>Search
</a>

// Outline button
<a href='page.php' class='btn btn-outline-primary'>
    <i class='fas fa-plus me-2'></i>Add New
</a>

// Large button
<button class='btn btn-success btn-lg'>
    <i class='fas fa-download me-2'></i>Download
</button>
```

### **Navigation**
```php
// The navigation is automatically included
// and adapts based on login status
// Shows different links for logged in/out users
```

## 🎛️ **Advanced Usage**

### **Custom Styling**
```php
<?php
require_once __DIR__ . '/includes/universal_template.php';

global $template;

$content = "
<div class='container py-5'>
    <h1>Custom Styled Page</h1>
    <div class='my-custom-class'>Custom content</div>
</div>";

$template->setTheme('default')
         ->setTitle('Custom Page')
         ->addCSS('.my-custom-class { border: 3px solid var(--primary-color); }')
         ->render($content);
?>
```

### **Custom JavaScript**
```php
$template->addJS('
    console.log("Custom JavaScript loaded!");
    // Your custom JS code here
');
```

### **Hide Navigation/Footer**
```php
$template->hideNavigation()
         ->hideFooter()
         ->render($content);
```

### **Theme Customization**
```php
$template->setTheme('custom')
         ->addCSS('
             :root {
                 --primary-color: #ff6b6b;
                 --secondary-color: #4ecdc4;
             }
         ');
```

## 📱 **Responsive Design**

The template system is fully responsive and includes:

- **Mobile-first approach**
- **Flexible grid system**
- **Touch-friendly buttons**
- **Optimized for all screen sizes**
- **Modern CSS Grid and Flexbox**

## 🎯 **Best Practices**

### **1. Choose the Right Theme**
- Use `default` for general pages
- Use `dashboard` for user dashboards
- Use `login` for authentication pages
- Use `admin` for admin panels

### **2. Structure Your Content**
```php
$content = "
<section class='hero-section'>
    <!-- Hero content -->
</section>

<section class='py-5'>
    <div class='container'>
        <!-- Page content -->
    </div>
</section>";
```

### **3. Use Semantic HTML**
```php
<div class='card'>
    <div class='card-header'>
        <h3>Section Title</h3>
    </div>
    <div class='card-body'>
        <!-- Content -->
    </div>
</div>
```

### **4. Leverage Bootstrap Classes**
- Use `container`, `row`, `col-md-*` for layout
- Use `text-center`, `text-start`, `text-end` for alignment
- Use `mb-3`, `mt-4`, `py-5` for spacing
- Use `d-flex`, `align-items-center` for flexbox

## 🔧 **Migration from Old Templates**

### **Before (Multiple files):**
```php
require_once 'includes/templates/header.php';
require_once 'includes/templates/footer.php';
// Complex setup...
```

### **After (Universal system):**
```php
require_once __DIR__ . '/includes/universal_template.php';
page($content, 'Page Title');
```

### **Migration Steps:**
1. Replace old header/footer includes with `require_once __DIR__ . '/includes/universal_template.php'`
2. Wrap your content in a variable
3. Use `page()`, `dashboard_page()`, `login_page()`, or `admin_page()`
4. Remove old CSS/JS includes (already included)
5. Test and adjust styling as needed

## 📋 **Complete Example**

```php
<?php
require_once __DIR__ . '/includes/universal_template.php';

$content = "
<!-- Hero Section -->
<section class='hero-section'>
    <div class='container'>
        <div class='row'>
            <div class='col-lg-8 mx-auto text-center'>
                <h1 class='display-4 fw-bold mb-4'>Welcome!</h1>
                <p class='lead mb-4'>This is a beautiful page using the universal template system.</p>
                <div class='d-flex justify-content-center gap-3'>
                    <a href='dashboard' class='btn btn-light btn-lg'>
                        <i class='fas fa-tachometer-alt me-2'></i>Dashboard
                    </a>
                    <a href='profile' class='btn btn-outline-light btn-lg'>
                        <i class='fas fa-user me-2'></i>Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class='py-5'>
    <div class='container'>
        <h2 class='section-title'>Features</h2>
        <div class='row g-4'>
            <div class='col-md-4'>
                <div class='card text-center h-100'>
                    <div class='card-body'>
                        <i class='fas fa-rocket fa-3x text-primary mb-3'></i>
                        <h5>Fast</h5>
                        <p>Lightning fast loading and rendering.</p>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-center h-100'>
                    <div class='card-body'>
                        <i class='fas fa-palette fa-3x text-success mb-3'></i>
                        <h5>Beautiful</h5>
                        <p>Modern, professional design system.</p>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-center h-100'>
                    <div class='card-body'>
                        <i class='fas fa-cog fa-3x text-info mb-3'></i>
                        <h5>Flexible</h5>
                        <p>Easy to customize and extend.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>";

page($content, 'My Awesome Page');
?>
```

## 🎉 **Ready to Use!**

Your universal template system is ready! It combines the best of:
- ✅ Your beautiful login page design
- ✅ Your modern dashboard styling
- ✅ Professional admin panel layout
- ✅ Clean, organized structure

**Test it out:**
- `clean_login.php` - Login page example
- `clean_dashboard.php` - Dashboard page example
- `universal_template_examples.php` - More examples

**Use it everywhere!** One system for all your pages. 🚀
