<?php

use Database\Database;

class CreateUsersTable {
    public function up() {
        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `email` VARCHAR(100) NOT NULL UNIQUE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down() {
        Database::getInstance()->query("DROP TABLE IF EXISTS `users`");
    }
}
