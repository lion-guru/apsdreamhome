<?php

namespace Tests\Database\Migrations\MigrationClasses;

use Tests\Database\Migration;

class CreatePropertiesTable extends Migration
{
    public function __construct($db)
    {
        parent::__construct($db);
        $this->tableName = 'properties';
    }

    public function up(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `price` DECIMAL(10, 2) NOT NULL,
                `bedrooms` TINYINT UNSIGNED NOT NULL,
                `bathrooms` DECIMAL(3, 1) NOT NULL,
                `area` INT UNSIGNED NULL,
                `address` VARCHAR(255) NOT NULL,
                `city` VARCHAR(100) NOT NULL,
                `state` VARCHAR(50) NOT NULL,
                `zip_code` VARCHAR(20) NULL,
                `type` ENUM('house', 'apartment', 'condo', 'townhouse') NOT NULL,
                `status` ENUM('available', 'pending', 'sold', 'rented') DEFAULT 'available',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_type` (`type`),
                INDEX `idx_status` (`status`),
                INDEX `idx_price` (`price`),
                INDEX `idx_location` (`city`, `state`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
            -- Insert test data
            INSERT INTO `{$this->tableName}` 
                (`title`, `description`, `price`, `bedrooms`, `bathrooms`, `area`, `address`, `city`, `state`, `zip_code`, `type`, `status`)
            VALUES
                ('Beautiful Family Home', 'Spacious 4 bedroom house with a large backyard', 350000.00, 4, 2.5, 2200, '123 Main St', 'Austin', 'TX', '78701', 'house', 'available'),
                ('Downtown Apartment', 'Modern apartment in the heart of the city', 180000.00, 2, 1.0, 900, '456 Downtown Ave', 'Austin', 'TX', '78702', 'apartment', 'available'),
                ('Luxury Condo', 'High-end condo with great views', 450000.00, 3, 2.0, 1600, '789 High St', 'Austin', 'TX', '78703', 'condo', 'available'),
                ('Cozy Townhouse', 'Perfect for small families', 275000.00, 3, 2.5, 1800, '101 Suburb Ln', 'Round Rock', 'TX', '78664', 'townhouse', 'available'),
                ('Ranch Style Home', 'Single story home with large lot', 320000.00, 3, 2.0, 2000, '202 Country Rd', 'Pflugerville', 'TX', '78660', 'house', 'pending');
        ");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `{$this->tableName}`");
    }
}
