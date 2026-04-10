<?php

/**
 * Migration: Create Visitor Tracking System
 * Tracks anonymous visitors for follow-up
 * Now uses existing leads table instead of separate tables
 */

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=apsdreamhome',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Creating visitor tracking system...\n";

    // Create visitor_sessions table
    $columnExists = $pdo->query("SHOW TABLES LIKE 'visitor_sessions'")->rowCount() > 0;
    if (!$columnExists) {
        $pdo->exec("
            CREATE TABLE visitor_sessions (
                id int(11) NOT NULL AUTO_INCREMENT,
                session_id varchar(255) NOT NULL,
                ip_address varchar(45) DEFAULT NULL,
                user_agent text,
                referrer text,
                landing_page varchar(500) DEFAULT NULL,
                first_visit timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                last_visit timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                page_views int(11) DEFAULT 1,
                time_on_site int(11) DEFAULT 0,
                is_converted tinyint(1) DEFAULT 0,
                converted_user_id int(11) DEFAULT NULL,
                converted_at timestamp NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY session_id (session_id),
                KEY ip_address (ip_address),
                KEY is_converted (is_converted),
                KEY converted_user_id (converted_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✅ Created visitor_sessions table\n";
    } else {
        echo "⏭️  visitor_sessions table already exists\n";
    }

    // Create visitor_page_views table
    $columnExists = $pdo->query("SHOW TABLES LIKE 'visitor_page_views'")->rowCount() > 0;
    if (!$columnExists) {
        $pdo->exec("
            CREATE TABLE visitor_page_views (
                id int(11) NOT NULL AUTO_INCREMENT,
                session_id varchar(255) NOT NULL,
                page_url varchar(500) NOT NULL,
                page_title varchar(255) DEFAULT NULL,
                visited_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                time_spent int(11) DEFAULT 0,
                PRIMARY KEY (id),
                KEY session_id (session_id),
                KEY page_url (page_url),
                KEY visited_at (visited_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✅ Created visitor_page_views table\n";
    } else {
        echo "⏭️  visitor_page_views table already exists\n";
    }

    echo "\n✅ Visitor tracking system created successfully!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
