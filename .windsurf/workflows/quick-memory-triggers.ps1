---
description: Quick Memory Triggers - Instant Reminders During Coding
auto_execution_mode: 3
---

# ⚡ QUICK MEMORY TRIGGERS

## 🚨 **CRITICAL CODING REMINDERS**

### **📁 FILE CREATION (ALWAYS CHECK):**

```
❌ ASK: Does file already exist?
✅ DO: Edit existing file instead
❌ NEVER: Create duplicate files
✅ ALWAYS: Use smart editing
```

### **🏗️ ARCHITECTURE (NEVER BREAK):**

```
✅ Controller: app/Http/Controllers/NameController.php
✅ Model: app/Models/ModelName.php
✅ View: app/views/pages/page-name.php
✅ Route: routes/web.php
❌ NEVER: .blade.php, resources/views/
```

### **🎨 VIEW STRUCTURE (ALWAYS USE):**

```php
<?php
$page_title = 'Page Title - APS Dream Home';
$page_description = 'Description';
include __DIR__ . '/../layouts/base.php';
?>
```

### **🎛️ CONTROLLER PATTERN (ALWAYS FOLLOW):**

```php
public function methodName()
{
    $this->requireLogin(); // If protected
    $this->render('pages/page-name', [
        'page_title' => 'Page Title - APS Dream Home'
    ]);
}
```

### **🔒 SECURITY (NEVER FORGET):**

```
✅ Sanitize ALL inputs
✅ Use prepared statements
✅ Add login protection to dashboards
❌ NEVER: Direct $_POST/$_GET usage
```

---

## 🎯 **FEATURE MEMORY (CURRENT PROJECT):**

### **🤖 AI Assistant:**

- Chat interface with real-time responses
- Property recommendation engine
- Natural language processing

### **📊 Analytics Dashboard:**

- Real-time charts and metrics
- User engagement tracking
- Export functionality

### **🏢 MLM System:**

- Network tree visualization
- Commission calculations
- Rank progression tracking

### **📱 WhatsApp Templates:**

- Template creation and management
- Message scheduling
- Campaign tracking

---

## 🚨 **ANTI-PATTERNS (NEVER DO):**

### **❌ MVC VIOLATIONS:**

- Business logic in views
- HTML output in models
- Database queries in controllers
- Mixed responsibilities

### **❌ FILE MISTAKES:**

- Creating duplicate files
- Using .blade.php extension
- Mixing Blade and PHP syntax
- Wrong file locations

### **❌ SECURITY RISKS:**

- Unsensitized user input
- Direct database queries
- Missing login protection
- No input validation

---

## 📋 **CODING CHECKLIST (QUICK):**

### **🔍 Before Coding:**

- [ ] Check if file exists
- [ ] Review architecture rules
- [ ] Plan implementation
- [ ] Prepare security measures

### **🔧 During Coding:**

- [ ] Follow MVC patterns
- [ ] Use proper naming
- [ ] Add security measures
- [ ] Test frequently

### **✅ After Coding:**

- [ ] Test functionality
- [ ] Check for duplicates
- [ ] Update documentation
- [ ] Commit changes

---

## 🎯 **NEXT PRIORITY (REMEMBER ORDER):**

1. **AI Property Valuation** - Market differentiator
2. **Advanced Analytics** - Business intelligence
3. **Mobile App MVP** - User accessibility
4. **Security Hardening** - Essential protection

---

## ⚡ **INSTANT TRIGGERS:**

### **📝 When Creating Controller:**

- Extend BaseController
- Add requireLogin() if protected
- Use $this->render()
- Follow naming convention

### **🎨 When Creating View:**

- Use .php extension only
- Include base layout
- Define page variables
- No Blade syntax

### **🗄️ When Working with Database:**

- Use prepared statements
- Sanitize inputs
- Handle errors properly
- Return data, not HTML

### **🛣️ When Adding Routes:**

- Add to routes/web.php
- Use RESTful patterns
- Protect sensitive routes
- Test accessibility

---

**⚡ MEMORY TRIGGERS ACTIVATED - NEVER FORGET!**

**Status:** INSTANT REMINDERS • Priority: CRITICAL • Authority: IMMUTABLE
