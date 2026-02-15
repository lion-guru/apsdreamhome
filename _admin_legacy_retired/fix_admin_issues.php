<?php
// Fix Common Admin Panel Issues

// 1. Create missing directories with proper permissions
function createMissingDirectories() {
    $directories = [
        '../uploads',
        '../assets/images',
        '../admin/uploads',
        '../includes/config'
    ];
    
    $results = [];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0755, true)) {
                $results[$dir] = 'Created successfully';
            } else {
                $results[$dir] = 'Failed to create';
            }
        } else {
            if (chmod($dir, 0755)) {
                $results[$dir] = 'Permissions updated';
            } else {
                $results[$dir] = 'Failed to update permissions';
            }
        }
    }
    
    return $results;
}

// 2. Create default admin user if not exists
function createDefaultAdmin() {
    require_once __DIR__ . '/includes/config/config.php';
    global $con;
    
    try {
        $conn = $con;
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows === 0) {
            return ['status' => 'error', 'message' => 'Users table does not exist'];
        }
        
        // Check if admin exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Create default admin
            $username = 'admin';
            $email = 'admin@apsdreamhome.com';
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $full_name = 'Administrator';
            $role = 'admin';
            $status = 'active';
            $created_at = date('Y-m-d H:i:s');
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $username, $email, $password, $full_name, $role, $status, $created_at);
            
            if ($stmt->execute()) {
                return [
                    'status' => 'success',
                    'message' => 'Default admin user created',
                    'username' => 'admin',
                    'password' => 'admin123',
                    'note' => 'Please change the default password immediately after login.'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to create admin user: ' . $conn->error
                ];
            }
        } else {
            return [
                'status' => 'info',
                'message' => 'Admin user already exists'
            ];
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// 3. Fix session configuration
function fixSessionConfig() {
    $sessionPath = session_save_path();
    $sessionDir = dirname(__FILE__) . '/../sessions';
    
    // Create session directory if it doesn't exist
    if (!file_exists($sessionDir)) {
        mkdir($sessionDir, 0755, true);
    }
    
    // Set session save path
    ini_set('session.save_path', $sessionDir);
    
    // Set session cookie parameters
    $lifetime = 86400; // 24 hours
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    return [
        'session_save_path' => $sessionDir,
        'session_cookie_params' => session_get_cookie_params()
    ];
}

// Run all fixes
$results = [
    'directories' => createMissingDirectories(),
    'admin_user' => createDefaultAdmin(),
    'session' => fixSessionConfig()
];

// Output results as JSON
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

// Add instructions
if (isset($results['admin_user']['status']) && $results['admin_user']['status'] === 'success') {
    echo "\n\nIMPORTANT: Default admin credentials have been created.\n";
    echo "Username: " . $results['admin_user']['username'] . "\n";
    echo "Password: " . $results['admin_user']['password'] . "\n\n";
    echo "Please log in and change the default password immediately.\n";
    echo "<a href='login.php'>Go to Admin Login</a>";
}
?>
