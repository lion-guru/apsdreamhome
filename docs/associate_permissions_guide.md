# 🛡️ Associate Permissions System - Complete Guide

## 📋 Overview

The Associate Permissions System provides **role-based access control (RBAC)** for associates based on their level in the MLM hierarchy. It determines what features, modules, and actions each associate can access.

## 🎯 How It Works

### **1. Authentication Flow**
```php
// When associate logs in:
1. Check session: $_SESSION['associate_logged_in']
2. Verify associate exists in database
3. Load associate level and permissions
4. Check if associate has access to requested module
5. Allow/Deny access based on permissions
```

### **2. Permission Levels by Associate Level**

| Level | Dashboard | Customers | CRM | Team Mgmt | Commission Mgmt | Reports | Admin |
|-------|-----------|-----------|-----|-----------|-----------------|---------|-------|
| **Associate** | ✅ Read | ✅ Read | ✅ Read | ❌ | ❌ | ❌ | ❌ |
| **Sr. Associate** | ✅ Read/Write | ✅ Read/Write | ✅ Read/Write | ❌ | ❌ | ✅ Read | ❌ |
| **BDM** | ✅ Read/Write | ✅ Read/Write | ✅ Read/Write | ✅ Read/Write | ❌ | ✅ Read/Write | ❌ |
| **Sr. BDM** | ✅ Read/Write | ✅ Read/Write | ✅ Admin | ✅ Read/Write | ✅ Read/Write | ✅ Read/Write | ❌ |
| **Vice President** | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ |
| **President** | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ |
| **Site Manager** | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ Admin | ✅ |

## 🔧 Key Functions

### **Check Permissions**
```php
// Check if associate can access a module
if (canAccessModule($associate_id, 'dashboard')) {
    // Show dashboard
}

// Check if associate can perform specific action
if (canPerformAction($associate_id, 'customers', 'write')) {
    // Allow editing customers
}

// Check if associate is admin
if (isAssociateAdmin($associate_id)) {
    // Show admin features
}
```

### **Get Accessible Modules**
```php
$accessible_modules = getAccessibleModules($associate_id);
// Returns array of modules associate can access
```

### **Update Permissions**
```php
// Grant permission
updateAssociatePermission($associate_id, 'reports', 'read', true);

// Revoke permission
updateAssociatePermission($associate_id, 'team_management', 'write', false);
```

## 📊 Database Structure

### **associate_permissions table**
```sql
CREATE TABLE associate_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    associate_id INT NOT NULL,
    module_name VARCHAR(50) NOT NULL,
    permission_type ENUM('read', 'write', 'delete', 'admin') NOT NULL,
    is_allowed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES mlm_agents(id) ON DELETE CASCADE
);
```

## 🎛️ Available Modules

| Module | Description | Actions |
|--------|-------------|---------|
| **dashboard** | Main dashboard | read, write |
| **customers** | Customer management | read, write, delete |
| **crm** | CRM system | read, write, admin |
| **team_management** | Team management | read, write, admin |
| **commission_management** | Commission management | read, write, admin |
| **reports** | Business reports | read, write, admin |
| **profile** | Profile management | read, write |

## 💡 Usage Examples

### **1. In Dashboard Pages**
```php
<?php
require_once 'includes/associate_permissions.php';

// Check access before showing content
if (!canAccessModule($associate_id, 'dashboard')) {
    header("Location: no_access.php");
    exit();
}
?>
```

### **2. In Navigation Menus**
```php
<?php
$accessible_modules = getAccessibleModules($associate_id);
?>

<nav>
    <?php if (isset($accessible_modules['customers'])): ?>
        <a href="customers.php">Customers</a>
    <?php endif; ?>

    <?php if (canPerformAction($associate_id, 'reports', 'write')): ?>
        <a href="reports.php">Reports</a>
    <?php endif; ?>
</nav>
```

### **3. In Action Buttons**
```php
<?php if (canPerformAction($associate_id, 'customers', 'write')): ?>
    <button onclick="editCustomer()">Edit Customer</button>
<?php endif; ?>

<?php if (canPerformAction($associate_id, 'customers', 'delete')): ?>
    <button onclick="deleteCustomer()">Delete Customer</button>
<?php endif; ?>
```

### **4. In Forms**
```php
<?php
if (isset($_POST['submit'])) {
    if (canPerformAction($associate_id, 'customers', 'write')) {
        // Process form submission
        updateCustomer($associate_id, $_POST);
    } else {
        $error = "You don't have permission to perform this action.";
    }
}
?>
```

## 🔒 Security Features

### **1. Session Validation**
- ✅ Validates associate login status
- ✅ Checks associate ID in database
- ✅ Verifies associate is active

### **2. Permission Checking**
- ✅ Checks module access before loading
- ✅ Validates actions before execution
- ✅ Level-based permission hierarchy

### **3. Error Handling**
- ✅ Logs permission violations
- ✅ Redirects unauthorized users
- ✅ Shows appropriate error messages

## 🎨 UI Integration

### **Dynamic Navigation**
```javascript
// Hide/show menu items based on permissions
$(document).ready(function() {
    // Only show accessible modules
    $('.nav-item').each(function() {
        var module = $(this).data('module');
        if (!canAccessModule(module)) {
            $(this).hide();
        }
    });
});
```

### **Conditional Content**
```php
<?php if (canPerformAction($associate_id, 'reports', 'admin')): ?>
    <div class="admin-panel">
        <!-- Admin only content -->
    </div>
<?php endif; ?>
```

## 📈 Permission Hierarchy

### **Associate Levels**
1. **Associate** (Entry Level)
2. **Sr. Associate** (Mid Level)
3. **BDM** (Business Development Manager)
4. **Sr. BDM** (Senior Business Development Manager)
5. **Vice President** (Senior Management)
6. **President** (Top Management)
7. **Site Manager** (Highest Level)

### **Permission Inheritance**
- Higher levels inherit lower level permissions
- Additional permissions granted at each level
- Admin permissions only for top 3 levels

## 🛠️ Maintenance

### **Adding New Modules**
1. Add to `$modules` array in `getAccessibleModules()`
2. Add permission checks in relevant files
3. Update navigation menus
4. Add to database seeder

### **Modifying Permissions**
1. Update level permissions in `checkLevelBasedPermission()`
2. Update database permissions table
3. Test with different associate levels

### **Monitoring**
- Check logs for permission violations
- Monitor unauthorized access attempts
- Review permission usage patterns

## 🎯 Best Practices

### **1. Always Check Permissions**
```php
// ✅ Good: Check before action
if (canPerformAction($associate_id, 'customers', 'delete')) {
    deleteCustomer($customer_id);
}

// ❌ Bad: Check after action
deleteCustomer($customer_id);
if (!canPerformAction($associate_id, 'customers', 'delete')) {
    // Too late!
}
```

### **2. Use Appropriate Permission Levels**
- Use `read` for viewing data
- Use `write` for modifying data
- Use `admin` for administrative functions
- Use `delete` for removing data

### **3. Cache Permissions (Optional)**
```php
// Cache permissions in session for better performance
if (!isset($_SESSION['permissions'])) {
    $_SESSION['permissions'] = getAssociatePermissions($associate_id);
}
```

## 🚨 Troubleshooting

### **Common Issues**

**1. Associate can't access dashboard**
- Check if `associate_logged_in` session is set
- Verify associate exists in database
- Check if dashboard permission is granted

**2. Navigation not showing**
- Verify accessible modules are loaded
- Check if permission checks are working
- Review browser console for JavaScript errors

**3. Permission denied errors**
- Check associate level in database
- Verify permission functions are included
- Review error logs for detailed messages

### **Debug Functions**
```php
// Debug permission issues
function debugPermissions($associate_id) {
    echo "Associate ID: $associate_id\n";
    echo "Permissions: " . print_r(getAssociatePermissions($associate_id), true);
    echo "Accessible Modules: " . print_r(getAccessibleModules($associate_id), true);
    echo "Is Admin: " . (isAssociateAdmin($associate_id) ? 'Yes' : 'No');
}
```

## 📝 Summary

The Associate Permissions System provides:
- ✅ **Role-based access control** based on associate level
- ✅ **Flexible permission management** for different modules
- ✅ **Security through validation** at every level
- ✅ **Easy maintenance** and updates
- ✅ **Comprehensive logging** for security monitoring

This system ensures that associates only see and can do what they're authorized to, based on their position in the MLM hierarchy.
