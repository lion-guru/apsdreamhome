---
description: APS Dream Home Development Rules - Never Break These
auto_execution_mode: 3
---

# APS DREAM HOME - WINDSURF WORKFLOW RULES

## 🚨 IMMUTABLE DEVELOPMENT RULES

### 1. 📁 FILE CREATION RULES

```bash
# ALWAYS create files in app/views/
# NEVER use resources/views/
# ALWAYS use .php extension
# NEVER use .blade.php extension

# ✅ CORRECT:
app/views/pages/new-page.php

# ❌ WRONG:
resources/views/new-page.blade.php
```

### 2. 🏗️ FILE STRUCTURE RULE

```php
// ✅ ALWAYS USE THIS PATTERN:
<?php
$page_title = 'Page Title';
$page_description = 'Description';
include __DIR__ . '/../layouts/base.php';
?>

<!-- HTML Content -->

// ❌ NEVER USE:
@extends('layouts/base')
@section('content')
```

### 3. 🎯 CONTROLLER RENDERING

```php
// ✅ ALWAYS USE:
$this->render('pages/page-name', [
    'page_title' => 'Page Title',
    'page_description' => 'Description'
]);

// ❌ NEVER USE Blade syntax
```

### 4. 🔍 BEFORE CREATING NEW FILES

```bash
# ALWAYS CHECK FIRST:
ls app/views/pages/
# Verify file doesn't exist
# Use .php extension only
```

### 5. 🧹 CLEANUP COMMANDS

```bash
# If Blade files appear:
find app/views -name "*.blade.php" -delete

# If duplicate directory appears:
rm -rf resources/views

# If mixed syntax found:
# Convert to pure PHP manually
```

## 🏗️ MVC STRUCTURE RULES

### Directory Structure (IMMUTABLE):

```
app/
├── Http/Controllers/     # Web controllers
├── Models/             # Data models
├── views/              # View files (.php only)
├── Services/           # Business logic
└── Core/               # Framework core
routes/
├── web.php            # Web routes
└── api.php            # API routes
```

### Controller Rules:

- ✅ Location: `app/Http/Controllers/NameController.php`
- ✅ Namespace: `App\Http\Controllers`
- ✅ Extend: `BaseController`
- ✅ Render: `$this->render('pages/page-name', [...])`

### Model Rules:

- ✅ Location: `app/Models/ModelName.php`
- ✅ Namespace: `App\Models`
- ✅ Database: Use prepared statements
- ✅ Return: Data only, no HTML

### View Rules:

- ✅ Location: `app/views/pages/page-name.php`
- ✅ Extension: `.php` only
- ✅ Include: `include __DIR__ . '/../layouts/base.php'`

### Route Rules:

- ✅ Web routes: `routes/web.php`
- ✅ API routes: `routes/api.php`
- ✅ Pattern: RESTful where applicable

## 📋 DEVELOPMENT CHECKLIST

### Before Starting Work:

- [ ] Check if file already exists
- [ ] Use correct directory (`app/views/`)
- [ ] Use correct extension (`.php`)
- [ ] Follow PHP include pattern
- [ ] No Blade syntax

### After Creating Files:

- [ ] Verify file loads correctly
- [ ] Check for syntax errors
- [ ] Test in browser
- [ ] Ensure no duplicate files created

### Before Committing:

- [ ] No `.blade.php` files exist
- [ ] No `resources/views/` directory
- [ ] All files use pure PHP
- [ ] Controllers use correct render method

## 🚨 EMERGENCY PROTOCOLS

### If Architecture Issues Detected:

1. **STOP** all work immediately
2. **CHECK** for duplicate files
3. **CLEANUP** using commands above
4. **VERIFY** single architecture
5. **CONTINUE** only after cleanup

### If Mixed Syntax Found:

1. **IDENTIFY** Blade syntax usage
2. **CONVERT** to pure PHP
3. **UPDATE** controllers if needed
4. **TEST** functionality
5. **DOCUMENT** the fix

## 🎯 SPECIFIC WORKFLOW RULES

### Creating New Pages:

1. Navigate to `app/views/pages/`
2. Create `new-page.php` (NOT `.blade.php`)
3. Use PHP include pattern
4. Add route in `routes/web.php`
5. Test immediately

### Modifying Existing Pages:

1. Open `.php` file (NOT `.blade.php`)
2. Maintain PHP structure
3. Update controller if needed
4. Test changes

### Debugging Issues:

1. Check file exists in `app/views/`
2. Verify `.php` extension
3. Check PHP syntax
4. Verify controller render call
5. Check route configuration

## 🔒 LOCKED PATTERNS

### Page Template:

```php
<?php
$page_title = 'Page Title';
$page_description = 'Description';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid">
    <!-- Content here -->
</div>

<style>
/* Styles here */
</style>

<script>
// Scripts here
</script>
```

### Controller Method:

```php
public function methodName()
{
    $this->requireLogin();

    $this->render('pages/page-name', [
        'page_title' => 'Page Title - APS Dream Home',
        'page_description' => 'Description'
    ]);
}
```

### Route Definition:

```php
$router->get('/route-name', 'ControllerName@methodName');
```

## 📞 HELP & REFERENCE

### Architecture Rules File:

- Location: `ARCHITECTURE_RULES.md`
- Contains: Permanent system rules
- Status: IMMUTABLE

### View System:

- Base Path: `app/views/`
- Extensions: `.php` only
- Pattern: PHP include
- No Blade syntax

### Common Issues:

- Duplicate files → Delete immediately
- Mixed syntax → Convert to PHP
- Wrong directory → Move to app/views/
- Wrong extension → Rename to .php

---

**⚠️ WARNING: Never break these rules!**
**🔒 This architecture is permanent!**
**📋 Follow checklist every time!**

**Created:** 2026-03-04  
**Status:** PERMANENT WORKFLOW RULES  
**Authority:** IMMUTABLE
