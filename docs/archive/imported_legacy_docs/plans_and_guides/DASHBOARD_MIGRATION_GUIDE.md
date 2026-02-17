# Dashboard Migration Guide - Session Helpers

यह guide सभी dashboard files को unified session helpers में migrate करने के लिए है।

## Quick Reference Pattern

### Before (Old Pattern):
```php
<?php
session_start();
require_once 'includes/config.php';

// Manual session check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
```

### After (New Pattern):
```php
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/session_helpers.php';

// Use helper functions
requireAuth('login.php');

$user_id = getAuthUserId();
$user_name = getDisplayName();
```

## Migration Steps for Each Dashboard

### Step 1: Add Session Helpers Include
```php
require_once 'includes/session_helpers.php';
// or for admin dashboards:
require_once __DIR__ . '/../includes/session_helpers.php';
```

### Step 2: Replace Manual Auth Checks

#### Pattern A: Simple Authentication Check
**Old:**
```php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
```

**New:**
```php
requireAuth('login.php');
```

#### Pattern B: Admin Authentication Check
**Old:**
```php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
```

**New:**
```php
requireAuth('admin/index.php');
if (!isAdmin()) {
    header('Location: admin/index.php?error=not_admin');
    exit();
}
```

#### Pattern C: Role-Specific Check
**Old:**
```php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'superadmin') {
    header('Location: dashboard.php');
    exit();
}
```

**New:**
```php
requireAdminRole('superadmin', 'admin/dashboard.php');
// or for multiple roles:
requireAdminRole(['superadmin', 'admin'], 'admin/dashboard.php');
```

#### Pattern D: Multiple Role Check
**Old:**
```php
if (!in_array($_SESSION['admin_role'], ['sales', 'marketing', 'manager'])) {
    header('Location: dashboard.php');
    exit();
}
```

**New:**
```php
requireAdminRole(['sales', 'marketing', 'manager'], 'admin/dashboard.php');
```

### Step 3: Replace Session Variable Access

#### User ID
**Old:**
```php
$user_id = $_SESSION['user_id'];
// or
$user_id = $_SESSION['uid'];
// or
$admin_id = $_SESSION['admin_id'];
```

**New:**
```php
$user_id = getAuthUserId();
```

#### Username
**Old:**
```php
$username = $_SESSION['username'];
// or
$username = $_SESSION['admin_username'];
// or
$username = $_SESSION['auser'];
```

**New:**
```php
$username = getAuthUsername();
```

#### User Name (Display Name)
**Old:**
```php
$name = $_SESSION['user_name'] ?? 'User';
// or
$name = $_SESSION['name'];
```

**New:**
```php
$name = getDisplayName(); // Has built-in fallback
```

#### Email
**Old:**
```php
$email = $_SESSION['uemail'];
// or
$email = $_SESSION['email'];
```

**New:**
```php
$email = getAuthUserEmail();
```

#### Role
**Old:**
```php
$role = $_SESSION['user_role'];
// or
$role = $_SESSION['admin_role'];
// or
$role = $_SESSION['utype'];
```

**New:**
```php
$role = getAuthRole(); // Returns: admin|associate|agent|employee|customer|user
$sub_role = getAuthSubRole(); // For admin: superadmin|director|manager|etc.
```

### Step 4: Replace Role Checks in Logic

#### Check if Admin
**Old:**
```php
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Admin logic
}
```

**New:**
```php
if (isAdmin()) {
    // Admin logic
}
```

#### Check if Specific Role
**Old:**
```php
if ($_SESSION['utype'] === 'associate') {
    // Associate logic
}
```

**New:**
```php
if (isAssociate()) {
    // Associate logic
}
// or use hasRole():
if (hasRole('associate')) {
    // Associate logic
}
```

#### Check Multiple Roles
**Old:**
```php
if (in_array($_SESSION['admin_role'], ['superadmin', 'admin', 'manager'])) {
    // Logic
}
```

**New:**
```php
if (hasSubRole(['superadmin', 'admin', 'manager'])) {
    // Logic
}
```

## Dashboard-Specific Examples

### Example 1: MLM Dashboard (Public)
```php
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/session_helpers.php';

// Require authentication and associate role
requireAuth('login.php');
if (!isAssociate() && !hasRole(['user', 'associate'])) {
    header('Location: index.php?error=not_associate');
    exit();
}

$user_id = getAuthUserId();
$user_name = getDisplayName();
// ... rest of dashboard code
```

### Example 2: Admin Role Dashboard
```php
<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session_helpers.php';

// Require admin authentication with specific role
requireAdminRole('sales', 'admin/dashboard.php');

$admin_id = getAuthUserId();
$admin_name = getDisplayName();
$admin_role = getAuthSubRole();
// ... rest of dashboard code
```

### Example 3: Multi-Role Admin Dashboard
```php
<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session_helpers.php';

// Allow multiple admin roles
requireAdminRole(['director', 'manager', 'ceo'], 'admin/dashboard.php');

$admin_id = getAuthUserId();
$admin_name = getDisplayName();
$admin_role = getAuthSubRole();
// ... rest of dashboard code
```

### Example 4: Agent Dashboard
```php
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/session_helpers.php';

// Require authentication and agent role
requireAuth('login.php');
requireRole('agent', 'index.php');

$agent_id = getAgentId(); // Special helper for agent ID
$user_id = getAuthUserId();
$agent_name = getDisplayName();
// ... rest of dashboard code
```

### Example 5: Employee Dashboard
```php
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/session_helpers.php';

// Require authentication and employee role
requireAuth('login.php');
requireRole('employee', 'index.php');

$employee_id = getAuthUserId();
$employee_name = getDisplayName();
// ... rest of dashboard code
```

## Common Patterns by Dashboard Type

### Public User Dashboards
- `mlm_dashboard.php` - Associate/MLM users
- `user_dashboard.php` - Regular users
- `agent_dashboard.php` - Agents
- `employee_dashboard.php` - Employees
- `customer_dashboard.php` - Customers
- `associate_dashboard.php` - Associates

**Pattern:**
```php
requireAuth('login.php');
requireRole('specific_role', 'index.php');
```

### Admin Dashboards (Root Level)
- `admin_dashboard.php`
- `superadmin_dashboard.php`

**Pattern:**
```php
requireAuth('admin/index.php');
if (!isAdmin()) {
    header('Location: admin/index.php');
    exit();
}
```

### Admin Role Dashboards (admin/ folder)
- `admin/sales_dashboard.php`
- `admin/marketing_dashboard.php`
- `admin/hr_dashboard.php`
- etc.

**Pattern:**
```php
requireAdminRole('specific_role', 'admin/dashboard.php');
```

## Testing Checklist

After migrating a dashboard, test:

- [ ] Login redirect works (unauthenticated users redirected to login)
- [ ] Role check works (wrong role redirected appropriately)
- [ ] User data displays correctly (name, email, etc.)
- [ ] Session timeout works (inactive users logged out)
- [ ] Logout works (session cleared properly)
- [ ] No PHP errors in error log
- [ ] No redirect loops

## Troubleshooting

### Issue: Redirect Loop
**Cause**: Dashboard redirects to login, login redirects back to dashboard
**Fix**: Check that login.php uses `setAuthSession()` correctly

### Issue: "Not Authenticated" Error
**Cause**: Session not set properly during login
**Fix**: Verify `setAuthSession()` is called in login handler

### Issue: Wrong Dashboard After Login
**Cause**: `dashboard.php` routing logic not using helpers
**Fix**: Ensure `dashboard.php` uses `getAuthRole()` for routing

### Issue: User Data Not Displaying
**Cause**: Helper functions returning null
**Fix**: Check that `setAuthSession()` receives correct user data array

## Migration Priority

### High Priority (Critical User Flows):
1. ✅ `mlm_dashboard.php` (DONE - Example)
2. `user_dashboard.php`
3. `associate_dashboard.php`
4. `agent_dashboard.php`
5. `employee_dashboard.php`
6. `customer_dashboard.php`

### Medium Priority (Admin Dashboards):
7. `admin/superadmin_dashboard.php`
8. `admin/sales_dashboard.php`
9. `admin/marketing_dashboard.php`
10. `admin/hr_dashboard.php`
11. `admin/it_dashboard.php`
12. `admin/operations_dashboard.php`
13. `admin/legal_dashboard.php`
14. `admin/finance_dashboard.php`

### Low Priority (Specialized Dashboards):
15. All other `admin/*_dashboard.php` files
16. Specialized role dashboards

## Batch Migration Script (Optional)

For bulk migration, you can use this PowerShell script:

```powershell
# Find all dashboard files
$dashboards = Get-ChildItem -Path . -Filter "*_dashboard.php" -Recurse

foreach ($file in $dashboards) {
    Write-Host "Migrating: $($file.FullName)"
    # Add migration logic here
    # This is a template - customize based on file patterns
}
```

## Notes

- **Backward Compatibility**: All helpers check both new and old session keys
- **No Breaking Changes**: Existing functionality preserved during migration
- **Gradual Migration**: Can migrate one dashboard at a time
- **Testing**: Test each dashboard after migration before moving to next

---

**Last Updated**: December 18, 2025  
**Status**: Phase 3 in progress  
**Next**: Migrate remaining high-priority dashboards
