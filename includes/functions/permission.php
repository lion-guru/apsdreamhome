<?php
/**
 * Permission Management Functions
 * 
 * This file contains functions for managing user permissions and access control
 * in the APS Dream Home system.
 */

/**
 * Check if the current user has the specified permission
 * 
 * @param string $permission The permission to check
 * @param bool $redirect Whether to redirect to access denied page if permission is denied
 * @return bool True if user has permission, false otherwise
 */
function require_permission($permission, $redirect = true) {
    // Make sure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['uid']) || !isset($_SESSION['role'])) {
        if ($redirect) {
            header("Location: /apsdreamhomefinal/admin/login.php");
            exit;
        }
        return false;
    }
    
    $userId = $_SESSION['uid'];
    $userRole = $_SESSION['role'];
    
    // Admin has all permissions
    if ($userRole === 'admin') {
        return true;
    }
    
    // Define role-based permissions
    $rolePermissions = [
        'admin' => [
            'view_dashboard',
            'manage_properties',
            'manage_users',
            'manage_leads',
            'manage_visits',
            'view_reports',
            'manage_settings',
            'view_associate_commission_report',
            'manage_commissions',
            'view_analytics',
            'manage_notifications',
            'export_data',
            'manage_api_keys'
        ],
        'agent' => [
            'view_dashboard',
            'manage_own_properties',
            'view_own_leads',
            'manage_own_visits',
            'view_own_reports',
            'view_associate_commission_report',
            'view_own_commissions'
        ],
        'associate' => [
            'view_dashboard',
            'view_associate_commission_report',
            'view_own_commissions',
            'view_own_network'
        ],
        'customer' => [
            'view_properties',
            'schedule_visits',
            'view_own_visits'
        ]
    ];
    
    // Check if the role has the required permission
    if (isset($rolePermissions[$userRole]) && in_array($permission, $rolePermissions[$userRole])) {
        return true;
    }
    
    // Check for user-specific permissions from database
    $userPermissions = get_user_permissions($userId);
    if (in_array($permission, $userPermissions)) {
        return true;
    }
    
    // Permission denied
    if ($redirect) {
        header("Location: /apsdreamhomefinal/admin/access_denied.php");
        exit;
    }
    
    return false;
}

/**
 * Get user-specific permissions from database
 * 
 * @param int $userId The user ID
 * @return array Array of permissions
 */
function get_user_permissions($userId) {
    global $con;
    
    // Default empty permissions array
    $permissions = [];
    
    // If database connection is not available, return empty array
    if (!isset($con) || !$con) {
        return $permissions;
    }
    
    // Check if user_permissions table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'user_permissions'";
    $tableResult = $con->query($tableCheckQuery);
    
    if ($tableResult && $tableResult->num_rows > 0) {
        // Query user permissions
        $query = "SELECT permission FROM user_permissions WHERE user_id = ?";
        $stmt = $con->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $permissions[] = $row['permission'];
            }
            
            $stmt->close();
        }
    }
    
    return $permissions;
}

/**
 * Check if access denied page exists, create if not
 */
function ensure_access_denied_page_exists() {
    $accessDeniedPath = __DIR__ . '/../../admin/access_denied.php';
    
    if (!file_exists($accessDeniedPath)) {
        $content = '<?php
// Access Denied Page
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            padding: 50px;
            background-color: #f8f9fa;
        }
        .access-denied {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .access-denied h1 {
            color: #dc3545;
        }
        .access-denied .icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="access-denied">
        <div class="icon">⚠️</div>
        <h1>Access Denied</h1>
        <p class="lead">You do not have permission to access this page.</p>
        <p>Please contact your administrator if you believe this is an error.</p>
        <div class="mt-4">
            <a href="/apsdreamhomefinal/admin/dashboard.php" class="btn btn-primary">Return to Dashboard</a>
            <a href="/apsdreamhomefinal/admin/logout.php" class="btn btn-secondary ms-2">Logout</a>
        </div>
    </div>
</body>
</html>';
        
        file_put_contents($accessDeniedPath, $content);
    }
}

// Ensure access denied page exists
ensure_access_denied_page_exists();
?>
