<?php
/**
 * Migration: Create page_versions table for CMS page edit audit trail
 *
 * Every edit to a CMS page (terms, privacy, refund policy, etc.) is automatically
 * saved as a version snapshot. Admins can view history and restore previous versions.
 *
 * Usage: php scripts/migrate_page_versions.php
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3307;
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: '2jcePXuNaOfEyo6I5wJVkG');
$db   = getenv('DB_DATABASE') ?: 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if table already exists
    $tables = $pdo->query("SHOW TABLES LIKE 'page_versions'")->fetchAll();
    if (!empty($tables)) {
        echo "[OK] Table 'page_versions' already exists. Skipping creation.\n";
    } else {
        $pdo->exec("
            CREATE TABLE page_versions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id INT UNSIGNED NOT NULL COMMENT 'FK to pages.id',
                version_number INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Sequential version for this page',
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                content LONGTEXT NOT NULL,
                meta_description TEXT DEFAULT NULL,
                meta_keywords VARCHAR(500) DEFAULT NULL,
                status ENUM('published','draft') NOT NULL DEFAULT 'draft',
                change_summary VARCHAR(500) DEFAULT NULL COMMENT 'Admin-entered note about what changed',
                changed_by INT UNSIGNED DEFAULT NULL COMMENT 'user_id of the admin who made the edit',
                changed_by_name VARCHAR(255) DEFAULT NULL COMMENT 'Denormalized name for display',
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_page_versions_page_id (page_id),
                INDEX idx_page_versions_page_version (page_id, version_number),
                INDEX idx_page_versions_changed_by (changed_by),
                INDEX idx_page_versions_tenant (tenant_id),
                INDEX idx_page_versions_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "[SUCCESS] Created 'page_versions' table.\n";
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT DEFAULT NULL,
        meta_description VARCHAR(500) DEFAULT NULL,
        meta_keywords VARCHAR(500) DEFAULT NULL,
        status ENUM('draft','published','archived') DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_pages_tenant_id (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed initial versions for all existing pages
    $stmt = $pdo->query("SELECT id, title, slug, content, meta_description, meta_keywords, status, tenant_id FROM pages ORDER BY id");
    $pages = $stmt->fetchAll();

    $insertStmt = $pdo->prepare("
        INSERT IGNORE INTO page_versions (page_id, version_number, title, slug, content, meta_description, meta_keywords, status, change_summary, changed_by_name, tenant_id, created_at)
        VALUES (?, 1, ?, ?, ?, ?, ?, ?, 'Initial version', 'System Migration', ?, NOW())
    ");

    $count = 0;
    foreach ($pages as $p) {
        // Check if version 1 already exists
        $check = $pdo->prepare("SELECT id FROM page_versions WHERE page_id = ? AND version_number = 1 LIMIT 1");
        $check->execute([$p['id']]);
        if ($check->fetch()) {
            continue; // Already seeded
        }
        $insertStmt->execute([
            $p['id'],
            $p['title'],
            $p['slug'],
            $p['content'],
            $p['meta_description'] ?? null,
            $p['meta_keywords'] ?? null,
            $p['status'],
            $p['tenant_id'],
        ]);
        $count++;
    }

    echo "[SUCCESS] Seeded {$count} initial version(s) for existing pages.\n";

    // Show summary
    $total = $pdo->query("SELECT COUNT(*) FROM page_versions")->fetchColumn();
    echo "[SUMMARY] page_versions table has {$total} version(s).\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
