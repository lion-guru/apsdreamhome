<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';

$db = new App\Core\Database();
$pdo = $db->getPdo();

$tables = [
    'auctions' => "CREATE TABLE IF NOT EXISTS auctions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        property_id BIGINT UNSIGNED NULL,
        plot_id BIGINT UNSIGNED NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT NULL,
        auction_type ENUM('english','sealed','dutch','reserve') NOT NULL DEFAULT 'english',
        start_price DECIMAL(15,2) NOT NULL,
        reserve_price DECIMAL(15,2) NULL,
        current_bid DECIMAL(15,2) NULL,
        bid_increment DECIMAL(12,2) NOT NULL DEFAULT 1000.00,
        buy_now_price DECIMAL(15,2) NULL,
        deposit_amount DECIMAL(12,2) NULL,
        starts_at DATETIME NOT NULL,
        ends_at DATETIME NOT NULL,
        original_ends_at DATETIME NOT NULL,
        extension_seconds INT UNSIGNED NOT NULL DEFAULT 60,
        auto_extend_threshold_seconds INT UNSIGNED NOT NULL DEFAULT 60,
        status ENUM('draft','scheduled','live','paused','ended','sold','cancelled') NOT NULL DEFAULT 'draft',
        winner_id BIGINT UNSIGNED NULL,
        winning_bid DECIMAL(15,2) NULL,
        bid_count INT UNSIGNED NOT NULL DEFAULT 0,
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        watcher_count INT UNSIGNED NOT NULL DEFAULT 0,
        image_url VARCHAR(500) NULL,
        gallery JSON NULL,
        terms TEXT NULL,
        created_by BIGINT UNSIGNED NULL,
        approved_by BIGINT UNSIGNED NULL,
        approved_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_ends (ends_at),
        INDEX idx_property (property_id),
        INDEX idx_plot (plot_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'auction_bids' => "CREATE TABLE IF NOT EXISTS auction_bids (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        auction_id BIGINT UNSIGNED NOT NULL,
        bidder_id BIGINT UNSIGNED NOT NULL,
        bidder_name VARCHAR(120) NOT NULL,
        bid_amount DECIMAL(15,2) NOT NULL,
        max_auto_bid DECIMAL(15,2) NULL,
        bid_type ENUM('manual','auto','proxy','buy_now') NOT NULL DEFAULT 'manual',
        ip_address VARCHAR(64) NULL,
        status ENUM('active','outbid','winning','won','lost','cancelled','invalid') NOT NULL DEFAULT 'active',
        placed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_auction (auction_id),
        INDEX idx_bidder (bidder_id),
        INDEX idx_status (status),
        INDEX idx_placed (placed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'auction_watchers' => "CREATE TABLE IF NOT EXISTS auction_watchers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        auction_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        notify_outbid TINYINT(1) NOT NULL DEFAULT 1,
        notify_ending TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_auction_user (auction_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'auction_deposits' => "CREATE TABLE IF NOT EXISTS auction_deposits (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        auction_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        payment_method VARCHAR(40) NULL,
        transaction_id VARCHAR(120) NULL,
        status ENUM('pending','paid','refunded','forfeited') NOT NULL DEFAULT 'pending',
        paid_at DATETIME NULL,
        refunded_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_auction_user (auction_id, user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $name\n";
    } catch (Exception $e) {
        echo "ERR: $name - " . $e->getMessage() . "\n";
    }
}

echo "Phase 55: Property Auction tables created (4 tables)\n";?>