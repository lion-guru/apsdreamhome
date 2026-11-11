<?php

namespace App\Database\Migrations;

use App\Core\Database\Database;

class CreateMessengerMessagesTable
{
    public static function up(): void
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            CREATE TABLE IF NOT EXISTS `messenger_messages` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `queue_name` VARCHAR(190) NOT NULL,
                `body` LONGTEXT NOT NULL,
                `available_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `IDX_queue_available` (`queue_name`, `available_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->exec($sql);
        echo "messenger_messages table created/verified\n";
    }
    
    public static function down(): void
    {
        $db = Database::getInstance()->getConnection();
        $db->exec("DROP TABLE IF EXISTS `messenger_messages`");
        echo "messenger_messages table dropped\n";
    }
}

// Run if called directly
if (php_sapi_name() === 'cli' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../..');
    $dotenv->load();
    require_once __DIR__ . '/../../app/Core/Bootstrap/base.php';
    
    CreateMessengerMessagesTable::up();
}