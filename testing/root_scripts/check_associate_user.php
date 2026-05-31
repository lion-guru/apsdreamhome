<?php
// Database configuration
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully!\n\n";
    
    // Check associate user in users table
    $stmt = $pdo->prepare("SELECT id, customer_id, name, email, phone, user_type, role, status, created_at FROM users WHERE email = ? AND user_type = 'associate' LIMIT 1");
    $stmt->execute(['associate@apsdreamhome.com']);
    $associate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($associate) {
        echo "Associate user found in users table:\n";
        echo "ID: " . $associate['id'] . "\n";
        echo "Customer ID: " . $associate['customer_id'] . "\n";
        echo "Name: " . $associate['name'] . "\n";
        echo "Email: " . $associate['email'] . "\n";
        echo "Phone: " . $associate['phone'] . "\n";
        echo "User Type: " . $associate['user_type'] . "\n";
        echo "Role: " . $associate['role'] . "\n";
        echo "Status: " . $associate['status'] . "\n";
        echo "Created: " . $associate['created_at'] . "\n\n";
        
        // Test password verification
        $password = 'password123';
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ? AND user_type = 'associate' LIMIT 1");
        $stmt->execute(['associate@apsdreamhome.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            echo "Password verification: SUCCESS\n";
        } else {
            echo "Password verification: FAILED\n";
            
            // Update password
            echo "Updating password...\n";
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = ? WHERE email = ? AND user_type = 'associate'");
            $stmt->execute([$hashedPassword, date('Y-m-d H:i:s'), 'associate@apsdreamhome.com']);
            echo "Password updated successfully!\n";
        }
        
        // Update status to active if not active
        if ($associate['status'] !== 'active') {
            echo "Updating status to active...\n";
            $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = ? WHERE email = ? AND user_type = 'associate'");
            $stmt->execute(['active', date('Y-m-d H:i:s'), 'associate@apsdreamhome.com']);
            echo "Status updated to active!\n";
        }
        
    } else {
        echo "Associate user not found in users table.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
