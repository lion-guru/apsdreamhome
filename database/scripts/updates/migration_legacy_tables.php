<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // 1. associate_levels
    $sql1 = "CREATE TABLE IF NOT EXISTS `associate_levels` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(50) NOT NULL,
      `commission_percent` decimal(5,2) NOT NULL,
      `direct_referral_bonus` decimal(5,2) DEFAULT 0.00,
      `level_bonus` decimal(5,2) DEFAULT 0.00,
      `reward_description` text DEFAULT NULL,
      `min_team_size` int(11) DEFAULT 0,
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      `min_business` decimal(15,2) NOT NULL DEFAULT 0.00,
      `max_business` decimal(15,2) NOT NULL DEFAULT 99999999.99,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    $db->exec($sql1);
    echo "Table 'associate_levels' created/checked.\n";

    // Seed associate_levels
    $seed1 = "INSERT INTO `associate_levels` (`id`, `name`, `commission_percent`, `direct_referral_bonus`, `level_bonus`, `reward_description`, `min_team_size`, `status`, `min_business`, `max_business`) VALUES
    (1, 'Starter', 5.00, 1.00, 0.00, 'Basic level for new associates', 0, 'active', 0.00, 500000.00),
    (2, 'Bronze', 7.00, 1.50, 0.50, 'Bronze level with increased commission', 3, 'active', 500001.00, 2000000.00),
    (3, 'Silver', 10.00, 2.00, 1.00, 'Silver level with higher rewards', 10, 'active', 2000001.00, 5000000.00),
    (4, 'Gold', 12.50, 2.50, 1.50, 'Gold level with premium benefits', 25, 'active', 5000001.00, 10000000.00),
    (5, 'Platinum', 15.00, 3.00, 2.00, 'Top level with maximum benefits', 50, 'active', 10000001.00, 999999999.00)
    ON DUPLICATE KEY UPDATE name=VALUES(name), commission_percent=VALUES(commission_percent);";
    
    $db->exec($seed1);
    echo "Table 'associate_levels' seeded.\n";

    // 2. company_property_levels
    $sql2 = "CREATE TABLE IF NOT EXISTS `company_property_levels` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `plan_id` int(11) NOT NULL,
      `level_name` varchar(100) NOT NULL,
      `level_order` int(11) NOT NULL,
      `direct_commission_percentage` decimal(5,2) NOT NULL,
      `team_commission_percentage` decimal(5,2) DEFAULT 0.00,
      `level_bonus_percentage` decimal(5,2) DEFAULT 0.00,
      `matching_bonus_percentage` decimal(5,2) DEFAULT 0.00,
      `leadership_bonus_percentage` decimal(5,2) DEFAULT 0.00,
      `monthly_target` decimal(15,2) NOT NULL,
      `min_plot_value` decimal(15,2) DEFAULT 0.00,
      `max_plot_value` decimal(15,2) DEFAULT 999999999.00,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    $db->exec($sql2);
    echo "Table 'company_property_levels' created/checked.\n";

    // Seed company_property_levels
    $seed2 = "INSERT INTO `company_property_levels` (`id`, `plan_id`, `level_name`, `level_order`, `direct_commission_percentage`, `team_commission_percentage`, `level_bonus_percentage`, `matching_bonus_percentage`, `leadership_bonus_percentage`, `monthly_target`, `min_plot_value`, `max_plot_value`) VALUES
    (1, 1, 'Associate', 1, 6.00, 2.00, 0.00, 0.00, 0.00, 1000000.00, 0.00, 10000000.00),
    (2, 1, 'Sr. Associate', 2, 8.00, 3.00, 1.00, 2.00, 0.00, 3500000.00, 10000000.00, 50000000.00),
    (3, 1, 'BDM', 3, 10.00, 4.00, 2.00, 3.00, 1.00, 7000000.00, 50000000.00, 150000000.00),
    (4, 1, 'Sr. BDM', 4, 12.00, 5.00, 3.00, 4.00, 2.00, 15000000.00, 150000000.00, 500000000.00),
    (5, 1, 'Vice President', 5, 15.00, 6.00, 4.00, 5.00, 3.00, 30000000.00, 500000000.00, 1000000000.00),
    (6, 1, 'President', 6, 18.00, 7.00, 5.00, 6.00, 4.00, 50000000.00, 1000000000.00, 9999999999.00),
    (7, 1, 'Site Manager', 7, 20.00, 8.00, 6.00, 7.00, 5.00, 100000000.00, 10000000000.00, 99999999999.00)
    ON DUPLICATE KEY UPDATE level_name=VALUES(level_name), direct_commission_percentage=VALUES(direct_commission_percentage);";

    $db->exec($seed2);
    echo "Table 'company_property_levels' seeded.\n";
    
    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
