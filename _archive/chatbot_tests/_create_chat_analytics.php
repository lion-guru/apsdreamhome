<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$db->exec('CREATE TABLE IF NOT EXISTS chat_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(20) NOT NULL,
    action VARCHAR(50) NOT NULL,
    session_id VARCHAR(100) NOT NULL,
    user_id INT UNSIGNED NULL,
    meta JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_event (event_type),
    INDEX idx_created (created_at),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
echo "chat_analytics table created\n";
