<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "Creating marketing_templates table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS `marketing_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `category` ENUM('festival','offer','launch','status','greeting','info') NOT NULL DEFAULT 'offer',
    `description` VARCHAR(255) NULL,
    `canvas_w` INT UNSIGNED NOT NULL DEFAULT 1080,
    `canvas_h` INT UNSIGNED NOT NULL DEFAULT 1080,
    `bg_color` VARCHAR(7) NOT NULL DEFAULT '#070C18',
    `accent_color` VARCHAR(7) NOT NULL DEFAULT '#FACC15',
    `title_text` VARCHAR(200) NULL,
    `subtitle_text` VARCHAR(200) NULL,
    `show_price` TINYINT(1) NOT NULL DEFAULT 1,
    `show_location` TINYINT(1) NOT NULL DEFAULT 1,
    `show_offer` TINYINT(1) NOT NULL DEFAULT 1,
    `show_branding` TINYINT(1) NOT NULL DEFAULT 1,
    `show_qr` TINYINT(1) NOT NULL DEFAULT 0,
    `photo_layout` ENUM('top','background','side','none') NOT NULL DEFAULT 'top',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_system` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=seeded, 0=admin-created',
    `sort_order` INT NOT NULL DEFAULT 0,
    `use_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_category` (`category`, `is_active`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Marketing post templates';
";

try {
    $db->exec($sql);
    echo "✅ marketing_templates created\n";
} catch (\Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nSeeding 12 ready templates...\n";

$templates = [
    // Festival (3)
    ['name' => 'Diwali Gold', 'slug' => 'diwali-gold', 'category' => 'festival', 'description' => 'Diwali greeting with gold theme', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#3C1400', 'accent_color' => '#FACC15', 'title_text' => 'Happy Diwali', 'subtitle_text' => 'शुभ दीपावली', 'show_price' => 0, 'show_location' => 0, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 0, 'photo_layout' => 'none', 'sort_order' => 1],
    ['name' => 'Holi Colors', 'slug' => 'holi-colors', 'category' => 'festival', 'description' => 'Holi greeting with vibrant theme', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#500A3C', 'accent_color' => '#F472B6', 'title_text' => 'Happy Holi', 'subtitle_text' => 'रंगों का त्योहार', 'show_price' => 0, 'show_location' => 0, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 0, 'photo_layout' => 'none', 'sort_order' => 2],
    ['name' => 'New Year Premium', 'slug' => 'newyear-premium', 'category' => 'festival', 'description' => 'New Year greeting, premium dark', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#070C28', 'accent_color' => '#60A5FA', 'title_text' => 'Happy New Year', 'subtitle_text' => 'नव वर्ष की शुभकामनाएं', 'show_price' => 0, 'show_location' => 0, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 0, 'photo_layout' => 'none', 'sort_order' => 3],
    // Offer (3)
    ['name' => 'Mega Discount', 'slug' => 'mega-discount', 'category' => 'offer', 'description' => 'Big discount announcement', 'canvas_w' => 1080, 'canvas_h' => 1920, 'bg_color' => '#070C18', 'accent_color' => '#F43F5E', 'title_text' => 'MEGA OFFER', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 1, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'top', 'sort_order' => 4],
    ['name' => 'Cashback Special', 'slug' => 'cashback-special', 'category' => 'offer', 'description' => '40% cashback highlight', 'canvas_w' => 1080, 'canvas_h' => 1920, 'bg_color' => '#052E16', 'accent_color' => '#34D399', 'title_text' => '40% CASHBACK', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 1, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'top', 'sort_order' => 5],
    ['name' => 'Zero EMI', 'slug' => 'zero-emi', 'category' => 'offer', 'description' => '0% EMI highlight', 'canvas_w' => 1080, 'canvas_h' => 1920, 'bg_color' => '#0C1A3C', 'accent_color' => '#60A5FA', 'title_text' => '0% EMI - 36 Months', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 1, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'top', 'sort_order' => 6],
    // Launch (2)
    ['name' => 'New Launch Gold', 'slug' => 'new-launch-gold', 'category' => 'launch', 'description' => 'New project launch announcement', 'canvas_w' => 1080, 'canvas_h' => 1920, 'bg_color' => '#070C18', 'accent_color' => '#FACC15', 'title_text' => 'NEW LAUNCH', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 1, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'top', 'sort_order' => 7],
    ['name' => 'New Phase Open', 'slug' => 'new-phase-open', 'category' => 'launch', 'description' => 'New phase booking open', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#1E1B4B', 'accent_color' => '#A78BFA', 'title_text' => 'BOOKINGS OPEN', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'background', 'sort_order' => 8],
    // Status (2)
    ['name' => 'Price Drop Alert', 'slug' => 'price-drop-alert', 'category' => 'status', 'description' => 'Price reduced announcement', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#450A0A', 'accent_color' => '#F87171', 'title_text' => 'PRICE DROP', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 0, 'photo_layout' => 'side', 'sort_order' => 9],
    ['name' => 'Sold Out', 'slug' => 'sold-out', 'category' => 'status', 'description' => 'Phase sold out announcement', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#1F2937', 'accent_color' => '#9CA3AF', 'title_text' => 'SOLD OUT', 'subtitle_text' => 'Thank you Gorakhpur!', 'show_price' => 0, 'show_location' => 1, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 0, 'photo_layout' => 'background', 'sort_order' => 10],
    // Greeting (1)
    ['name' => 'Good Morning Wish', 'slug' => 'good-morning', 'category' => 'greeting', 'description' => 'Daily good morning with branding', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#0C2A4A', 'accent_color' => '#FBBF24', 'title_text' => 'Good Morning', 'subtitle_text' => 'सुप्रभात', 'show_price' => 0, 'show_location' => 0, 'show_offer' => 0, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'none', 'sort_order' => 11],
    // Info (1)
    ['name' => 'Rate List Card', 'slug' => 'rate-list-card', 'category' => 'info', 'description' => 'Clean rate card with price', 'canvas_w' => 1080, 'canvas_h' => 1080, 'bg_color' => '#FFFFFF', 'accent_color' => '#0D9488', 'title_text' => 'RATE LIST', 'subtitle_text' => '', 'show_price' => 1, 'show_location' => 1, 'show_offer' => 1, 'show_branding' => 1, 'show_qr' => 1, 'photo_layout' => 'side', 'sort_order' => 12],
];

foreach ($templates as $t) {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO marketing_templates (name, slug, category, description, canvas_w, canvas_h, bg_color, accent_color, title_text, subtitle_text, show_price, show_location, show_offer, show_branding, show_qr, photo_layout, is_active, is_system, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, ?, NOW())");
        $stmt->execute([$t['name'], $t['slug'], $t['category'], $t['description'], $t['canvas_w'], $t['canvas_h'], $t['bg_color'], $t['accent_color'], $t['title_text'], $t['subtitle_text'], $t['show_price'], $t['show_location'], $t['show_offer'], $t['show_branding'], $t['show_qr'], $t['photo_layout'], $t['sort_order']]);
        echo "✅ {$t['name']} ({$t['category']})\n";
    } catch (\Exception $e) {
        echo "❌ {$t['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";