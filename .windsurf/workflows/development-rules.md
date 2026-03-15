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

//# ❌ NEVER USE:
//@extends('layouts/base')
//@section('content')
```

### 3. 🔍 DUPLICATE FILE ANALYSIS RULE (CRITICAL)

**Before deleting ANY duplicate file, ALWAYS follow this protocol:**

#### **Phase 1: COMPLETE ANALYSIS**

```bash
# Compare both files line-by-line
# Identify unique features in each file
# Document differences and advantages
```

#### **Phase 2: FEATURE EXTRACTION**

```bash
# Extract all unique/valuable features from duplicate
# Identify which version has superior implementation
# Note any Laravel/external dependencies
```

#### **Phase 3: SMART MERGE**

```bash
# Merge important features into main file
# Remove Laravel/external dependencies
# Maintain Custom MVC pattern
# Preserve ALL functionality
```

#### **Phase 4: CLEANUP**

```bash
# Remove duplicate file only after successful merge
# Test all merged functionality
# Document the merge process
```

**📋 EXAMPLE CASE STUDY: CoreFunctionsController**

- Original: 14 basic methods
- Duplicate: 35+ advanced methods with Laravel dependencies
- Solution: Merged 23+ methods into single file without dependencies

**🎯 GOLDEN RULE: "Duplicate files are not mistakes - they're evolution stages with unique value!"**

### 4. 🎯 CONTROLLER RENDERING

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
├── Controllers/    #  MVC controllers (NO Laravel)
├── Models/             # Data models
├── Services/           # Business logic
├── Services/Custom/    #  MVC services (NO Laravel)
├── Core/               # Framework core
├── views/              # View files (.php only)
└── Legacy/             # Legacy files being migrated
routes/
├── web.php            # Web routes
└── api.php            # API routes
```

### Controller Rules:

#### Standard Laravel Controllers:

- ✅ Location: `app/Http/Controllers/NameController.php`
- ✅ Namespace: `App\Http\Controllers`
- ✅ Extend: `BaseController`
- ✅ Render: `$this->render('pages/page-name', [...])`

#### MVC Controllers:

- ✅ Location: `app/Controllers/NameController.php`
- ✅ Namespace: `App\Controllers\`
- ✅ NO Laravel dependencies
- ✅ Use services: `new \App\Services\ServiceName()`
- ✅ Use view renderer: `new \App\Core\ViewRenderer()`
- ✅ Handle POST/GET with `$_POST`/`$_GET`
- ✅ Custom redirect method

### Model Rules:

- ✅ Location: `app/Models/ModelName.php`
- ✅ Namespace: `App\Models`
- ✅ Database: Use prepared statements
- ✅ Return: Data only, no HTML

### MVC Service Rules:

- ✅ Location: `app/Services/ServiceName.php`
- ✅ Namespace: `App\Services\
- ✅ NO Laravel dependencies
- ✅ Use database: `\App\Core\Database::getInstance()`
- ✅ Use logger: `new \App\Core\Logger()`
- ✅ Use config: `\App\Core\Config::getInstance()`
- ✅ Use session: `new \App\Core\Session()`
- ✅ Return format: `['success' => bool, 'data' => mixed, 'message' => string]`

### View Rules:

- ✅ Location: `app/views/pages/page-name.php`
- ✅ Extension: `.php` only
- ✅ Include: `include __DIR__ . '/../layouts/base.php'`

### Route Rules:

- ✅ Web routes: `routes/web.php`
- ✅ API routes: `routes/api.php`
- ✅ Pattern: RESTful where applicable

### Custom MVC Testing Rules:

- ✅ Location: `tests/Feature/ServiceNameTest.php`
- ✅ Namespace: `Tests\Feature\`
- ✅ Extend: `PHPUnit\Framework\TestCase`
- ✅ NO Laravel testing traits
- ✅ Test all service methods
- ✅ Clean up test data in tearDown()
- ✅ Follow database patterns

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
