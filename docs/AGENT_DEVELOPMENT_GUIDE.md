# 🚀 APS Dream Home - Agent Development Guide

## 📋 IMMEDIATE START CHECKLIST

### Before You Start (2 minutes)
- [ ] Read this entire guide first
- [ ] Check you have PHP environment ready
- [ ] Verify project location: `c:\xampp\htdocs\apsdreamhomefinal`
- [ ] Have PROJECT_MASTER_ANALYSIS.md open for reference

### Quick Start (15 minutes)
1. **Enable Development Mode**
   ```
   Visit: http://localhost/apsdreamhomefinal/development_mode.php
   Click: "Development Mode चालू करें"
   ```

2. **Test Current Setup**
   ```
   Visit: http://localhost/apsdreamhomefinal/test-ui.php
   Should show: Modern UI demo page
   ```

3. **Create Basic Structure**
   ```bash
   # Run these commands in project root:
   mkdir -p app/views/pages
   mkdir -p app/views/components
   mkdir -p app/views/layouts
   mkdir -p assets/css
   mkdir -p assets/js
   ```

---

## 🎯 DAILY TASK TEMPLATE

### Day 1: Foundation (Today)
**Morning (30 minutes):**
- [ ] Set up development environment
- [ ] Create folder structure
- [ ] Implement SimpleRouter.php
- [ ] Test basic routing

**Afternoon (45 minutes):**
- [ ] Create base template system
- [ ] Build header/footer components
- [ ] Create homepage with modern design
- [ ] Test responsive layout

**Evening (15 minutes):**
- [ ] Document progress
- [ ] Test all created pages
- [ ] Plan next day's work

### Day 2: Core Pages
**Morning (45 minutes):**
- [ ] Create property listing page
- [ ] Implement property cards
- [ ] Add basic search functionality
- [ ] Test property display

**Afternoon (45 minutes):**
- [ ] Create property detail page
- [ ] Add image gallery
- [ ] Implement contact form
- [ ] Test property navigation

**Evening (15 minutes):**
- [ ] Review and test all pages
- [ ] Fix any responsive issues
- [ ] Document completed work

### Day 3: Authentication
**Morning (45 minutes):**
- [ ] Create login page
- [ ] Create registration page
- [ ] Implement form validation
- [ ] Test authentication flow

**Afternoon (45 minutes):**
- [ ] Create customer dashboard
- [ ] Add user profile section
- [ ] Implement logout functionality
- [ ] Test user sessions

**Evening (15 minutes):**
- [ ] Security testing
- [ ] Session management check
- [ ] Document authentication system

---

## 🔧 STEP-BY-STEP CODING GUIDE

### Step 1: Create SimpleRouter.php (Copy from quick-start-guide.md)
```php
<?php
// app/core/SimpleRouter.php
class SimpleRouter {
    private $routes = [];
    
    public function add($route, $file) {
        $this->routes[$route] = $file;
    }
    
    public function dispatch($url) {
        $url = strtok($url, '?');
        $url = rtrim($url, '/');
        
        if (isset($this->routes[$url])) {
            $file = $this->routes[$url];
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        
        return false;
    }
}
?>
```

### Step 2: Update index.php
```php
<?php
require_once 'app/core/SimpleRouter.php';

$router = new SimpleRouter();

// Public routes
$router->add('', 'app/views/pages/home.php');
$router->add('home', 'app/views/pages/home.php');
$router->add('properties', 'app/views/pages/properties.php');
$router->add('property', 'app/views/pages/property-detail.php');
$router->add('login', 'app/views/pages/login.php');

// Get current URL
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Dispatch route
if (!$router->dispatch($url)) {
    header("HTTP/1.0 404 Not Found");
    require_once 'app/views/pages/404.php';
}
?>
```

### Step 3: Create Base Template
```php
<?php
// app/views/layouts/base.php
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'APS Dream Home'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <?php require_once 'app/views/components/header.php'; ?>
    
    <main class="main-content">
        <?php echo $content; ?>
    </main>
    
    <?php require_once 'app/views/components/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
```

### Step 4: Create Homepage
```php
<?php
// app/views/pages/home.php
ob_start();
?>

<div class="hero-section">
    <div class="container">
        <h1>आपका सपनों का घर खोजें</h1>
        <p>APS Dream Home - विश्वसनीय और आधुनिक रियल एस्टेट समाधान</p>
        <a href="/properties" class="btn btn-primary btn-lg">प्रॉपर्टी देखें</a>
    </div>
</div>

<div class="featured-properties container my-5">
    <h2>फीचर्ड प्रॉपर्टीज</h2>
    <div class="row">
        <!-- Property cards will go here -->
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'APS Dream Home - आपका सपनों का घर';
require_once 'app/views/layouts/base.php';
?>
```

---

## 🧪 TESTING CHECKLIST

### After Each Page Creation:
- [ ] Page loads without errors
- [ ] Mobile responsive design works
- [ ] All links are working
- [ ] Images display correctly
- [ ] Forms validate properly
- [ ] Navigation works smoothly

### Browser Testing:
- [ ] Chrome (primary)
- [ ] Firefox
- [ ] Safari (if available)
- [ ] Edge

### Device Testing:
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

---

## 🚨 COMMON ERRORS & SOLUTIONS

### Error: "Page not found"
**Solution:** Check development mode is enabled in development_mode.php

### Error: "CSS not loading"
**Solution:** Check file paths in base.php template

### Error: "Images not showing"
**Solution:** Verify image paths and file permissions

### Error: "Mobile layout broken"
**Solution:** Check viewport meta tag is present

### Error: "Forms not submitting"
**Solution:** Check form action URLs and method attributes

---

## 📊 PROGRESS TRACKING

### Daily Progress Sheet:
```
Date: ___________
Agent: ___________

Completed Today:
- [ ] Task 1: ________________
- [ ] Task 2: ________________
- [ ] Task 3: ________________

Issues Found:
- Issue 1: ________________
- Issue 2: ________________

Tomorrow's Plan:
- Task 1: ________________
- Task 2: ________________
- Task 3: ________________

Notes: ________________
```

### Weekly Milestones:
- **Week 1**: Foundation + Homepage working
- **Week 2**: All core pages complete
- **Week 3**: Advanced features added
- **Week 4**: Testing and optimization

---

## 🎯 QUALITY STANDARDS

### Code Quality:
- Use consistent indentation (4 spaces)
- Add comments for complex logic
- Follow naming conventions
- Keep functions small and focused

### UI/UX Standards:
- Mobile-first design approach
- Consistent color scheme (#2c3e50, #3498db, #27ae60)
- Proper spacing and typography
- Smooth transitions and animations

### Performance:
- Optimize images before upload
- Minimize HTTP requests
- Use CSS/JS minification
- Implement lazy loading

---

## 📞 ESCALATION PROCEDURE

### If Stuck for >30 minutes:
1. Check this guide again
2. Review PROJECT_MASTER_ANALYSIS.md
3. Check quick-start-guide.md
4. Document the exact issue
5. Ask for help with specific problem

### Emergency Contacts:
- Development Mode: `development_mode.php`
- Test Page: `test-ui.php`
- All Guides: Multiple .md files in root

---

## 🏆 SUCCESS CRITERIA

### Daily Success:
- [ ] All planned tasks completed
- [ ] No broken pages
- [ ] Mobile responsive design
- [ ] Code is clean and commented
- [ ] Progress documented

### Project Success:
- [ ] All pages working and responsive
- [ ] Clean, organized file structure
- [ ] Modern, attractive UI/UX
- [ ] Fast loading performance
- [ ] Easy to maintain and extend

---

**Remember: Start simple, test often, build incrementally. Focus on getting basic functionality working first, then enhance gradually.**

**Total Estimated Time to Working Site: 3-4 days of focused work**

**Next Step: Start with Step 1 above and follow the daily checklist!**