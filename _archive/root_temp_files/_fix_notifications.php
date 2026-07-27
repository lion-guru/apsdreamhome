<?php
$host='127.0.0.1'; $port=3307; $user='root'; $pass=''; $db='apsdreamhome';
try {
    $pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create push_notifications table
    $pdo->exec('CREATE TABLE IF NOT EXISTS push_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL DEFAULT "",
        body TEXT,
        data JSON,
        status VARCHAR(20) DEFAULT "sent",
        sent_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    echo 'push_notifications: OK'.PHP_EOL;

    // 2. Create notification_settings table
    $pdo->exec('CREATE TABLE IF NOT EXISTS notification_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        channel VARCHAR(20) NOT NULL,
        enabled TINYINT(1) DEFAULT 1,
        preferences JSON,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_user_channel (user_id, channel),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    echo 'notification_settings: OK'.PHP_EOL;

    echo 'ALL DONE';
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage().PHP_EOL;
}
