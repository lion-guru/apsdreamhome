<?php

namespace Tests\Database\Seeders;

use PDO;
use PDOException;

class TestDatabaseSeeder
{
    /**
     * Run the database seeds.
     */
    public static function run(PDO $pdo): void
    {
        self::createDatabaseIfNotExists($pdo);
        self::createTables($pdo);
    }

    /**
     * Create the test database if it doesn't exist.
     */
    private static function createDatabaseIfNotExists(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . TEST_DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . TEST_DB_NAME . "`");
        } catch (PDOException $e) {
            die("Failed to create test database: " . $e->getMessage());
        }
    }

    /**
     * Create the database tables.
     */
    private static function createTables(PDO $pdo): void
    {
        $tables = [
            'users' => "
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `email_verified_at` timestamp NULL DEFAULT NULL,
                    `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
                    `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `users_email_unique` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",
            'properties' => "
                CREATE TABLE IF NOT EXISTS `properties` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `description` text COLLATE utf8mb4_unicode_ci,
                    `price` decimal(15,2) NOT NULL,
                    `bedrooms` int(11) DEFAULT NULL,
                    `bathrooms` decimal(3,1) DEFAULT NULL,
                    `area` int(11) DEFAULT NULL,
                    `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `type` enum('house','apartment','condo','townhouse') COLLATE utf8mb4_unicode_ci NOT NULL,
                    `status` enum('available','pending','sold','rented') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            "
        ];

        try {
            $pdo->beginTransaction();

            foreach ($tables as $table => $sql) {
                $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
                $pdo->exec($sql);
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            die("Failed to create tables: " . $e->getMessage());
        }
    }
}
