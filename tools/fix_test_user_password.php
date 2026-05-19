<?php
// Fix test user password to Test@123
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Fixing test user password...\n";

    $newHash = password_hash('Test@123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$newHash, 'testuser@example.com']);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Password updated successfully for testuser@example.com\n";
        echo "New password is: Test@123\n";
        
        // Verify the update
        $stmt = $db->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute(['testuser@example.com']);
        $hash = $stmt->fetchColumn();
        
        if (password_verify('Test@123', $hash)) {
            echo "✅ Password verification PASSED\n";
        } else {
            echo "❌ Password verification FAILED\n";
        }
    } else {
        echo "❌ No user updated (user not found)\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>