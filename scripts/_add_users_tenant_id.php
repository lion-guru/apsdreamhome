<?php
require __DIR__ . '/../config/bootstrap.php';
$db = App\Core\Database\Database::getInstance()->getConnection();

try {
    $db->exec("ALTER TABLE users ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id");
    echo "Added tenant_id to users" . PHP_EOL;
} catch (Exception $e) {
    echo "Column: " . $e->getMessage() . PHP_EOL;
}

try {
    $db->exec("CREATE INDEX idx_users_tenant ON users(tenant_id)");
    echo "Added index" . PHP_EOL;
} catch (Exception $e) {
    echo "Index: " . $e->getMessage() . PHP_EOL;
}

// Verify
$r = $db->query("SHOW COLUMNS FROM users LIKE 'tenant_id'")->fetch();
echo "Result: " . ($r ? 'SUCCESS' : 'FAILED') . PHP_EOL;
