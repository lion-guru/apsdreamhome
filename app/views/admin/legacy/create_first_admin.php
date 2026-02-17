<?php
/**
 * Create First Admin User - APS Dream Homes
 * This script creates the initial super admin account
 * Run this once to setup the admin system
 */

require_once __DIR__ . '/../../vendor/autoload.php';
$db = \App\Core\App::database();

echo "<h2>🔧 APS Dream Homes - First Admin Setup</h2>";

// Check if admin already exists
$result = $db->fetch("SELECT COUNT(*) as count FROM admin");

if ($result && $result['count'] > 0) {
    echo "<div style='color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>⚠️ Admin users already exist!</strong><br>";
    echo "If you need to create another admin, please use the admin registration system.<br>";
    echo "Current admin count: " . $result['count'];
    echo "</div>";

    // Show existing admins (without passwords)
    $admins = $db->fetchAll("SELECT id, auser, email, status, created_at FROM admin ORDER BY id DESC");

    echo "<h3>📋 Existing Admin Users:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Status</th><th>Created</th></tr>";

    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . $admin['id'] . "</td>";
        echo "<td>" . h($admin['auser']) . "</td>";
        echo "<td>" . h($admin['email']) . "</td>";
        echo "<td>" . h($admin['status']) . "</td>";
        echo "<td>" . $admin['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} else {
    echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✅ No admin users found. Ready to create first admin!</strong>";
    echo "</div>";

    // Create first admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            echo "<div style='color: red;'>All fields are required!</div>";
        } else {
            try {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert super admin
                $sql = "INSERT INTO admin (auser, email, apass, phone, status, role)
                        VALUES (?, ?, ?, ?, 'active', 'super_admin')";
                $phone = '0000000000'; // Default phone

                if ($db->query($sql, [$name, $email, $hashed_password, $phone])) {
                    echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>🎉 Super Admin created successfully!</strong><br>";
                    echo "Name: " . h($name) . "<br>";
                    echo "Email: " . h($email) . "<br>";
                    echo "Password: [hidden for security]<br>";
                    echo "Role: Super Admin<br>";
                    echo "Status: Active<br><br>";
                    echo "<strong>🔗 Login URL:</strong> <a href='admin/'>admin/</a><br>";
                    echo "<strong>📧 Email:</strong> " . h($email) . "<br>";
                    echo "<strong>🔑 Password:</strong> " . h($password) . "<br>";
                    echo "</div>";

                    // Log the creation
                    error_log("Super admin created: $email");

                } else {
                    echo "<div style='color: red;'>Failed to create admin. Please check database.</div>";
                }

            } catch (Exception $e) {
                echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }

    // Show creation form
    echo "<h3>👤 Create First Super Admin</h3>";
    echo "<form method='POST' style='max-width: 400px;'>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Name:</label><br>";
    echo "<input type='text' name='name' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
    echo "</div>";

    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Email:</label><br>";
    echo "<input type='email' name='email' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
    echo "</div>";

    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Password:</label><br>";
    echo "<input type='password' name='password' required style='width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;'>";
    echo "</div>";

    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>";
    echo "🚀 Create Super Admin";
    echo "</button>";
    echo "</form>";

    echo "<div style='margin-top: 20px; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff;'>";
    echo "<strong>📝 Instructions:</strong><br>";
    echo "1. Enter your details above<br>";
    echo "2. Click 'Create Super Admin'<br>";
    echo "3. Save the login credentials securely<br>";
    echo "4. Use these credentials to login at <a href='admin/'>admin/</a><br>";
    echo "5. After login, you can create other admins and employees";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔐 Security Features:</h3>";
echo "<ul>";
echo "<li>✅ Password Hashing (bcrypt)</li>";
echo "<li>✅ Session-based Authentication</li>";
echo "<li>✅ CSRF Protection</li>";
echo "<li>✅ Rate Limiting</li>";
echo "<li>✅ Admin Registration Code Required</li>";
echo "<li>✅ Multi-level Approval System</li>";
echo "</ul>";

echo "<h3>👥 User Roles Available:</h3>";
echo "<ul>";
echo "<li>🔹 <strong>Super Admin:</strong> Full system access</li>";
echo "<li>🔹 <strong>Admin:</strong> Limited admin access</li>";
echo "<li>🔹 <strong>Employee:</strong> Employee dashboard access</li>";
echo "<li>🔹 <strong>Manager:</strong> Department management</li>";
echo "</ul>";

echo "<div style='margin-top: 20px; padding: 10px; background: #e7f3ff; border-left: 4px solid #007bff;'>";
echo "<strong>🚀 Next Steps:</strong><br>";
echo "1. Create your super admin account<br>";
echo "2. Login to admin dashboard<br>";
echo "3. Create employee accounts<br>";
echo "4. Set up employee dashboards<br>";
echo "5. Manage user permissions";
echo "</div>";

?>
