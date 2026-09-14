<?php
/**
 * Phase 1 Migration: Create missing tables + Fix incomplete features
 * Tables: possession_checklist, defect_reports, blog_comments
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Phase 1 Migration ===\n\n";
    
    // 1. possession_checklist
    echo "Creating possession_checklist...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `possession_checklist` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `booking_id` int(11) NOT NULL,
        `item_name` varchar(255) NOT NULL,
        `is_completed` tinyint(1) NOT NULL DEFAULT 0,
        `completed_by` int(11) DEFAULT NULL,
        `completed_at` timestamp NULL DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_booking_id` (`booking_id`),
        KEY `idx_tenant_id` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  ✓ Created\n";
    
    // 2. defect_reports
    echo "Creating defect_reports...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `defect_reports` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `booking_id` int(11) NOT NULL,
        `reported_by` int(11) DEFAULT NULL,
        `defect_type` varchar(100) NOT NULL DEFAULT 'general',
        `description` text NOT NULL,
        `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
        `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
        `resolution_notes` text DEFAULT NULL,
        `resolved_by` int(11) DEFAULT NULL,
        `resolved_at` timestamp NULL DEFAULT NULL,
        `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_booking_id` (`booking_id`),
        KEY `idx_status` (`status`),
        KEY `idx_tenant_id` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  ✓ Created\n";
    
    // 3. blog_comments
    echo "Creating blog_comments...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `blog_comments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `user_id` int(11) DEFAULT NULL,
        `parent_id` int(11) DEFAULT NULL,
        `author_name` varchar(100) DEFAULT NULL,
        `author_email` varchar(150) DEFAULT NULL,
        `content` text NOT NULL,
        `status` enum('pending','approved','spam','rejected') NOT NULL DEFAULT 'pending',
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_post_id` (`post_id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_status` (`status`),
        KEY `idx_tenant_id` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  ✓ Created\n";
    
    // 4. financial_inquiries (schema exists in SQL, create if missing)
    echo "Creating financial_inquiries (if not exists)...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `financial_inquiries` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
        `name` varchar(100) NOT NULL,
        `email` varchar(150) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `service_interest` varchar(100) NOT NULL DEFAULT 'general',
        `message` text DEFAULT NULL,
        `status` enum('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
        `assigned_to` int(11) DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_status` (`status`),
        KEY `idx_tenant_id` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "  ✓ Created\n";
    
    // 5. Seed blog_comments with test data for verification
    echo "Seeding blog_comments test data...\n";
    try {
        $hasBlogTable = (bool)$db->query("SHOW TABLES LIKE 'blog_posts'")->fetch();
    } catch (\Throwable $e) { $hasBlogTable = false; }
    if ($hasBlogTable) {
        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE status = 'published' LIMIT 1");
        $stmt->execute();
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($post) {
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM blog_comments WHERE post_id = ?");
            $stmt->execute([$post['id']]);
            $cnt = $stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;
            if ($cnt == 0) {
                $db->exec("INSERT INTO blog_comments (post_id, author_name, author_email, content, status, tenant_id) VALUES 
                    ({$post['id']}, 'Rahul Kumar', 'rahul@example.com', 'Great article! Very informative about real estate trends.', 'approved', 1),
                    ({$post['id']}, 'Priya Singh', 'priya@example.com', 'Very helpful for first-time buyers. Thank you!', 'approved', 1),
                    ({$post['id']}, 'Test User', 'test@example.com', 'This is a pending comment for testing.', 'pending', 1)
                ");
                echo "  ✓ Seeded 3 test comments\n";
            } else {
                echo "  ✓ Comments already exist\n";
            }
        } else {
            echo "  ⚠ No published blog posts found, skipping seed\n";
        }
    } else {
        echo "  ⚠ blog_posts table does not exist, skipping seed\n";
    }
    
    echo "\n=== Phase 1 Migration Complete ===\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
