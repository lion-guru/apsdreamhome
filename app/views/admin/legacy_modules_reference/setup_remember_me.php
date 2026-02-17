<?php
require_once __DIR__ . '/../../app/core/App.php';

try {
    $db = \App\Core\App::database();

    $sql = "CREATE TABLE IF NOT EXISTS remember_me_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->execute($sql);

    echo "Table 'remember_me_tokens' created successfully.";

} catch (Exception $e) {
    die("Error setting up remember_me_tokens table: " . $e->getMessage());
}
?>
