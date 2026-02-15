# 🚀 Simple Development Template System

## Overview
This is a lightweight template system designed for development phase. It's simple, easy to use, and doesn't require complex setup.

## Quick Start

### 1. Basic Page
```php
<?php
require_once __DIR__ . '/includes/simple_template.php';

$content = "
<div class='container py-5'>
    <h1>Hello World!</h1>
    <p>This is my page content.</p>
</div>";

simple_page($content, 'My Page Title');
?>
```

### 2. Page without Navigation
```php
simple_page($content, 'Page Title', false); // false = no nav
```

### 3. Page without Footer
```php
simple_page($content, 'Page Title', true, false); // false = no footer
```

## Available Functions

### Core Functions

#### `simple_header($title, $show_nav)`
- Outputs the HTML head and navigation
- `$title` - Page title
- `$show_nav` - Show navigation bar (default: true)

#### `simple_footer($show_footer)`
- Outputs the footer and scripts
- `$show_footer` - Show footer (default: true)

#### `simple_page($content, $title, $show_nav, $show_footer)`
- Complete page with header, content, and footer
- All parameters optional with sensible defaults

### Helper Functions

#### `simple_alert($message, $type)`
Creates alert messages:
```php
simple_alert('Success message!', 'success');
simple_alert('Error message!', 'danger');
simple_alert('Warning!', 'warning');
simple_alert('Info message!', 'info');
```

#### `simple_card($title, $content, $class)`
Creates Bootstrap cards:
```php
simple_card('Card Title', 'Card content here');
simple_card('Stats', '<h3>123</h3>', 'text-center');
```

#### `simple_button($text, $url, $class, $icon)`
Creates styled buttons:
```php
simple_button('Click Me', 'page.php', 'btn-primary', 'arrow-right');
```

### Development Helpers

#### `debug_session()`
Shows session data when `?debug=session` is in URL

#### `debug_post()`
Shows POST data when `?debug=post` is in URL

#### `debug_query($query, $params)`
Shows SQL query info when `?debug=sql` is in URL

## Examples

### Example 1: Simple Page
```php
<?php
require_once __DIR__ . '/includes/simple_template.php';

$content = "
<div class='container py-5'>
    <h1>Welcome!</h1>
    <p>This is a simple page.</p>
    " . simple_button('Go to Dashboard', 'dashboard.php') . "
</div>";

simple_page($content, 'Welcome Page');
?>
```

### Example 2: Dashboard with Stats
```php
<?php
require_once __DIR__ . '/includes/simple_template.php';

$content = "
<div class='container py-5'>
    <h1>Dashboard</h1>

    <div class='row'>
        <div class='col-md-3'>
            " . simple_card('Users', '1,234', 'text-center') . "
        </div>
        <div class='col-md-3'>
            " . simple_card('Orders', '567', 'text-center') . "
        </div>
        <div class='col-md-3'>
            " . simple_card('Revenue', '$12,345', 'text-center') . "
        </div>
        <div class='col-md-3'>
            " . simple_card('Growth', '+23%', 'text-center') . "
        </div>
    </div>
</div>";

simple_page($content, 'Dashboard');
?>
```

### Example 3: Form Page
```php
<?php
require_once __DIR__ . '/includes/simple_template.php';

$content = "
<div class='container py-5'>
    <div class='row justify-content-center'>
        <div class='col-md-6'>
            " . simple_card('Contact Form', '
                <form>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            ') . "
        </div>
    </div>
</div>";

simple_page($content, 'Contact Us');
?>
```

## File Structure

```
your-project/
├── includes/
│   └── simple_template.php      # Main template functions
├── simple_index.php             # Example homepage
├── simple_login.php             # Example login page
├── simple_template_examples.php # More examples
└── your-pages.php               # Your pages
```

## Benefits

✅ **Simple to use** - Just include and call functions
✅ **Fast development** - No complex setup required
✅ **Consistent design** - Built-in Bootstrap styling
✅ **Flexible** - Easy to customize and extend
✅ **Lightweight** - Minimal overhead
✅ **Development-friendly** - Includes debug helpers

## Customization

### Adding Custom Styles
Add custom CSS in the `<style>` section of `simple_template.php`

### Adding Custom Scripts
Add scripts before the closing `</body>` tag in `simple_footer()`

### Modifying Navigation
Edit the navigation HTML in the `simple_header()` function

## Tips for Development

1. **Use the debug helpers** - Add `?debug=session` to any URL to see session data
2. **Start with simple_page()** - It's the easiest way to create pages
3. **Use the helper functions** - They save time and ensure consistency
4. **Keep it simple** - This is for development, not production

## Migration from Complex Templates

If you have existing pages with complex header/footer includes:

**Before:**
```php
<?php
require_once 'includes/templates/header.php';
require_once 'includes/templates/footer.php';
?>

<!-- Your content -->

<?php
// Footer already included
?>
```

**After:**
```php
<?php
require_once __DIR__ . '/includes/simple_template.php';

$content = "<!-- Your content -->";

simple_page($content, 'Page Title');
?>
```

Much simpler! 🎉

## Ready to Use

Your simple template system is ready! Check out the example files:
- `simple_index.php` - Homepage example
- `simple_login.php` - Login page example
- `simple_template_examples.php` - More examples

Start building your pages with this simple system and focus on your application logic rather than template complexity!
