# Template Migration Guide

## Public Pages Migration
Replace:
```php
<?php include 'header.php'; ?>
```

With:
```php
<?php include 'includes/unified_header.php'; ?>
```

## Admin Pages Migration
Replace:
```php
<?php include 'admin/header.php'; ?>
```

With:
```php
<?php include 'admin/updated-admin-wrapper.php'; ?>
```

## Footer Updates
Replace all footer includes with:
```php
<?php include 'includes/unified_footer.php'; ?> // Public pages
<?php include 'admin/updated-admin-footer.php'; ?> // Admin pages
```
