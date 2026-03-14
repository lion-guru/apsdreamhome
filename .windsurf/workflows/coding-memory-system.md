---
description: Coding Memory System - Remember Everything During Development
auto_execution_mode: 3
---

# 🧠 CODING MEMORY SYSTEM

## 📝 ACTIVE CODING SESSION MEMORY

### 🎯 **CURRENT PROJECT STATUS:**

- **Project:** APS Dream Home
- **Type:** Real Estate + MLM Platform
- **Architecture:** Custom PHP MVC (Strict No-Blade Policy)
- **Database:** MySQL (597 tables)
- **Status:** Production Ready (Audit Completed: 2026-03-09)
- **Components:** Controllers(126), Models(128), Views(230)

### 🚨 **CRITICAL AUDIT FINDINGS (FIXED):**
- **Blade Syntax Removed:** Converted `users.php`, `properties.php`, `logout.php` to pure PHP.
- **Admin Layouts Created:** `app/views/admin/layouts/header.php` & `footer.php` created.
- **Path Definitions:** `APP_PATH` defined in `index.php` & `base.php`.
- **Legacy Cleanup:** Blade files in `storage/backups` ignored.

### 🏗️ **ARCHITECTURE RULES (NEVER FORGET):**

```
✅ Views: app/views/pages/page-name.php (.php ONLY)
✅ Controllers: app/Http/Controllers/NameController.php
✅ Models: app/Models/ModelName.php
✅ Routes: routes/web.php
✅ Include: include __DIR__ . '/../layouts/base.php'
❌ NEVER: .blade.php, resources/views/, Blade syntax
```

### 🚫 **DUPLICATE POLICY (CRITICAL):**

- **NEVER** create duplicate files
- **ALWAYS** edit existing files
- **CHECK** before creating new files
- **ENHANCE** existing functionality
- **CLEANUP** any duplicates found

### 🤖 **FEATURES TO REMEMBER:**

1. **AI Assistant** - Chat interface, property recommendations
2. **Analytics Dashboard** - Business intelligence, charts
3. **MLM System** - Network marketing, commissions
4. **WhatsApp Templates** - Message management
5. **Security** - Input sanitization, login protection

### 📋 **CODING CHECKLIST (ALWAYS FOLLOW):**

- [ ] Check if file exists before creating
- [ ] Use .php extension only
- [ ] Include base layout properly
- [ ] Add login protection to dashboards
- [ ] Sanitize all inputs
- [ ] Use prepared statements for DB
- [ ] Follow MVC separation
- [ ] Test after changes

---

## 🎯 **IMPLEMENTATION PRIORITY (REMEMBER ORDER):**

### **Phase 1 (Immediate):**

1. **AI Property Valuation Engine** - Market differentiator
2. **Advanced Analytics Dashboard** - Business intelligence
3. **Mobile App MVP** - User accessibility
4. **Security Hardening** - Essential protection

### **Phase 2 (Next):**

1. **MLM Advanced Features** - Revenue growth
2. **Social Integration** - User engagement
3. **Performance Optimization** - Scalability
4. **Marketing Automation** - Lead generation

### **Phase 3 (Future):**

1. **Global Expansion** - Market growth
2. **API Ecosystem** - Developer community
3. **Monetization** - Revenue streams
4. **Enterprise Features** - Premium clients

---

## 🧠 **MEMORY TRIGGERS (CODING TIME):**

### **📝 When Creating Controllers:**

```php
// REMEMBER THIS PATTERN:
<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class FeatureController extends BaseController
{
    public function index()
    {
        $this->requireLogin(); // If protected
        $this->render('pages/feature-name', [
            'page_title' => 'Feature Name - APS Dream Home',
            'page_description' => 'Feature description'
        ]);
    }
}
```

### **🎨 When Creating Views:**

```php
// REMEMBER THIS STRUCTURE:
<?php
$page_title = 'Page Title - APS Dream Home';
$page_description = 'Page description';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid">
    <!-- Content here -->
</div>
```

### **🛣️ When Adding Routes:**

```php
// REMEMBER TO ADD TO routes/web.php:
$router->get('/feature-name', 'FeatureController@index');
```

### **🗄️ When Working with Database:**

```php
// REMEMBER SECURITY:
$stmt = $this->db->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
// NEVER direct queries with user input
```

---

## 🚨 **CRITICAL REMINDERS:**

### **🔒 Security (NEVER FORGET):**

- Sanitize ALL user inputs
- Use prepared statements
- Add login protection to dashboards
- Check user permissions
- Validate file uploads

### **🏗️ Architecture (ALWAYS MAINTAIN):**

- MVC separation is mandatory
- Controllers handle request/response
- Models handle database only
- Views handle presentation only
- Routes handle URL mapping only

### **📁 File Organization (STRICT):**

- Controllers in `app/Http/Controllers/`
- Models in `app/Models/`
- Views in `app/views/pages/`
- Routes in `routes/web.php`
- NO exceptions to this structure

### **🚫 Anti-Patterns (NEVER DO):**

- Don't mix business logic in views
- Don't output HTML in models
- Don't handle database in controllers
- Don't create duplicate files
- Don't use Blade syntax

---

## 🎯 **NEXT CODING SESSION REMINDERS:**

### **📋 Before Starting:**

1. **Check existing files** - Don't create duplicates
2. **Follow architecture rules** - MVC compliance
3. **Use proper naming** - Consistent patterns
4. **Add security** - Input sanitization
5. **Test functionality** - Verify works

### **🔧 During Development:**

1. **Use smart editing** - Enhance existing files
2. **Follow patterns** - Consistent code style
3. **Add comments** - Document complex logic
4. **Backup files** - Before major changes
5. **Test frequently** - Catch issues early

### **✅ After Completion:**

1. **Test all routes** - Verify functionality
2. **Check for duplicates** - Clean up if found
3. **Update documentation** - Keep docs current
4. **Commit changes** - Use GitKraken
5. **Update monitoring** - Track progress

---

## 🌟 **FEATURE SPECIFIC MEMORY:**

### **🤖 AI Assistant:**

- Chat interface with real-time responses
- Property recommendation engine
- Natural language processing
- Integration with property database

### **📊 Analytics Dashboard:**

- Real-time charts and metrics
- User engagement tracking
- Property performance data
- Export functionality

### **🏢 MLM System:**

- Network tree visualization
- Commission calculations
- Rank progression tracking
- Payout management

### **📱 WhatsApp Templates:**

- Template creation and management
- Message scheduling
- Campaign tracking
- Analytics and reporting

---

## 📞 **QUICK REFERENCE (CODING TIME):**

### **🎯 Controller Pattern:**

```php
public function methodName()
{
    $this->requireLogin(); // If protected
    $data = $this->model->getData();
    $this->render('pages/page-name', [
        'page_title' => 'Page Title - APS Dream Home',
        'data' => $data
    ]);
}
```

### **🎨 View Pattern:**

```php
<?php
$page_title = 'Page Title - APS Dream Home';
$page_description = 'Description';
include __DIR__ . '/../layouts/base.php';
?>
```

### **🗄️ Model Pattern:**

```php
public function getData()
{
    $stmt = $this->db->prepare("SELECT * FROM table");
    $stmt->execute();
    return $stmt->fetchAll();
}
```

### **🛣️ Route Pattern:**

```php
$router->get('/route-name', 'ControllerName@methodName');
```

---

**🧠 MEMORY SYSTEM ACTIVATED - NEVER FORGET THESE RULES!**

**Status:** ACTIVE • Priority: CRITICAL • Authority: IMMUTABLE
