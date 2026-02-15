<?php
// Database connection details
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Function to display messages
function showMessage($message, $type = 'info') {
    $color = 'black';
    if ($type == 'success') {
        $color = 'green';
    } elseif ($type == 'error') {
        $color = 'red';
    }
    echo "<div style='color: $color;'>$message</div>";
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    showMessage("✅ Database connection successful!", 'success');
} catch (PDOException $e) {
    showMessage("❌ Database connection failed: " . $e->getMessage(), 'error');
    exit;
}

$constraints_to_drop = [
    'associates' => ['fk_associate_user'],
    'bookings' => ['bookings_ibfk_2'],
    'customers' => ['customers_ibfk_1'],
    'employees' => ['employees_ibfk_1'],
    'invoices' => ['invoices_ibfk_1'],
    'lead_notes' => ['fk_lead_notes_user_id'],
    'lead_status_history' => ['fk_lead_status_history_changed_by'],
    'mlm_tree' => ['mlm_tree_ibfk_1', 'mlm_tree_ibfk_2'],
    'notifications' => ['notifications_ibfk_1'],
    'notification_settings' => ['notification_settings_ibfk_1'],
    'payment_logs' => ['payment_logs_ibfk_1'],
    'project_categories' => ['project_categories_ibfk_1'],
    'properties' => ['properties_ibfk_2', 'properties_ibfk_3'],
    'property_reviews' => ['property_reviews_ibfk_2'],
    'rental_properties' => ['rental_properties_ibfk_1'],
    'rent_payments' => ['rent_payments_ibfk_2']
];

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    showMessage("Temporarily disabled foreign key checks.", 'info');

    foreach ($constraints_to_drop as $table => $constraints) {
        foreach ($constraints as $constraint) {
            try {
                // We also need to drop the index associated with the foreign key.
                // In MySQL, dropping the foreign key often drops the associated index if it was created implicitly.
                // However, if an index was created separately, it might need to be dropped separately.
                // For now, we'll just drop the foreign key.
                $pdo->exec("ALTER TABLE `$table` DROP FOREIGN KEY `$constraint`");
                showMessage("✅ Dropped constraint `$constraint` from table `$table`.", 'success');
            } catch (PDOException $e) {
                // It might fail if the constraint or table doesn't exist, which is okay in this cleanup script.
                showMessage("⚠️ Could not drop constraint `$constraint` from table `$table`. It might not exist. (Error: " . $e->getMessage() . ")", 'info');
            }
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    showMessage("✅ Re-enabled foreign key checks.", 'success');
    showMessage("Foreign key cleanup process finished.", 'success');

} catch (PDOException $e) {
    showMessage("❌ An error occurred during cleanup: " . $e->getMessage(), 'error');
    // Ensure checks are re-enabled on failure
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
?>