# APS DREAM HOME - PERMANENT ARCHITECTURE RULES

## 🚨 IMMUTABLE RULES - NEVER CHANGE THESE

### 1. 📁 VIEW SYSTEM RULE
- **ONLY USE:** `app/views/` directory
- **NEVER USE:** `resources/views/` (DELETED)
- **FILE EXTENSION:** `.php` ONLY (NEVER `.blade.php`)
- **SYNTAX:** Pure PHP (NO Blade syntax)

### 2. 🎯 CONTROLLER RENDERING
```php
// ALWAYS USE THIS FORMAT:
$this->render('pages/page-name', [
    'page_title' => 'Page Title',
    'page_description' => 'Description'
]);

// NEVER USE:
@extends() @section() @endsection
```

### 3. 📄 PAGE STRUCTURE
```php
<?php
$page_title = 'Page Title';
$page_description = 'Description';
include __DIR__ . '/../layouts/base.php';
?>

<!-- HTML Content Here -->
```

### 4. 🔧 LAYOUT INCLUSION
```php
// ALWAYS USE:
include __DIR__ . '/../layouts/base.php';

// NEVER USE:
@extends('layouts/base')
```

### 5. 🏗️ FILE ORGANIZATION
```
app/views/
├── layouts/
│   └── base.php (ONLY layout file)
├── pages/
│   ├── ai-assistant.php
│   ├── ai-dashboard.php
│   ├── analytics-dashboard.php
│   ├── mlm-dashboard.php
│   └── whatsapp-templates.php
└── [other directories...]
```

### 6. ⚡ PERFORMANCE RULES
- NO template engine overhead
- Direct PHP execution
- Single file lookup
- No dual-path checking

### 7. 🛡️ ERROR PREVENTION
- ALWAYS check file exists before creating
- NEVER create duplicate files
- ALWAYS use `.php` extension
- NEVER mix Blade and PHP

## 🚨 EMERGENCY CLEANUP COMMANDS

### If Blade files appear:
```bash
# DELETE ALL BLADE FILES
find app/views -name "*.blade.php" -delete
```

### If resources/views appears:
```bash
# DELETE DUPLICATE DIRECTORY
rm -rf resources/views
```

### If mixed syntax found:
```bash
# CONVERT TO PURE PHP
# Manual conversion required
```

## 📋 CHECKLIST FOR NEW FILES

✅ File extension is `.php`
✅ Uses `include __DIR__ . '/../layouts/base.php'`
✅ No Blade syntax (`{{ }}`, `@extends`, etc.)
✅ Located in `app/views/`
✅ Variables defined with `$page_title = '';`

## 🎯 FINAL VERIFICATION

Before committing changes:
1. ✅ No `.blade.php` files exist
2. ✅ No `resources/views/` directory
3. ✅ All pages use pure PHP
4. ✅ Controllers use `$this->render()`
5. ✅ Layouts use `include` pattern

## 🔒 LOCKED ARCHITECTURE

THIS IS THE FINAL ARCHITECTURE.
NO MORE CHANGES.
NO MORE EXPERIMENTS.
CONSISTENT PATTERN FOREVER.

---

**Created:** 2026-03-04
**Status:** PERMANENT
**Authority:** IMMUTABLE
