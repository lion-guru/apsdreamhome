<?php
// Simple database connection for test users
try {
    // Direct database connection
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== CREATING TEST ASSOCIATE USER ===\n";

    // Check if associate already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'associate'");
    $stmt->execute(['associate@apsdreamhome.com']);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "Associate user already exists. Updating password...\n";

        // Update password
        $hashedPassword = password_hash('associate123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, status = 'active', updated_at = ? WHERE id = ?");
        $updateResult = $stmt->execute([$hashedPassword, date('Y-m-d H:i:s'), $existing['id']]);

        if ($updateResult) {
            echo "Associate password updated successfully!\n";
        } else {
            echo "Failed to update associate password\n";
        }
    } else {
        echo "Creating new associate user...\n";

        // Insert associate user
        $hashedPassword = password_hash('associate123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, email, phone, password, role, status, 
                mlm_rank, commission_rate, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insertResult = $stmt->execute([
            'Test Associate',
            'associate@apsdreamhome.com',
            '9876543210',
            $hashedPassword,
            'associate',
            'active',
            'Associate',
            5.00,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);

        if ($insertResult) {
            echo "Associate user created successfully!\n";
        } else {
            echo "Failed to create associate user\n";
        }
    }

    // Also create a test customer user
    echo "\n=== CREATING TEST CUSTOMER USER ===\n";

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'customer'");
    $stmt->execute(['customer@apsdreamhome.com']);
    $existingCustomer = $stmt->fetch();

    if ($existingCustomer) {
        echo "Customer user already exists. Updating password...\n";

        $hashedPassword = password_hash('customer123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, status = 'active', updated_at = ? WHERE id = ?");
        $updateResult = $stmt->execute([$hashedPassword, date('Y-m-d H:i:s'), $existingCustomer['id']]);

        if ($updateResult) {
            echo "Customer password updated successfully!\n";
        }
    } else {
        echo "Creating new customer user...\n";

        $hashedPassword = password_hash('customer123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, email, phone, password, role, status, 
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insertResult = $stmt->execute([
            'Test Customer',
            'customer@apsdreamhome.com',
            '9876543211',
            $hashedPassword,
            'customer',
            'active',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);

        if ($insertResult) {
            echo "Customer user created successfully!\n";
        } else {
            echo "Failed to create customer user\n";
        }
    }

    echo "\n=== TEST CREDENTIALS ===\n";
    echo "ADMIN: admin@apsdreamhome.com / admin123\n";
    echo "ASSOCIATE: associate@apsdreamhome.com / associate123\n";
    echo "CUSTOMER: customer@apsdreamhome.com / customer123\n";
    echo "\nAll users are now ready for dashboard testing!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
