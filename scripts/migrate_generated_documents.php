<?php
/**
 * Migration: Create generated_documents table
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Creating generated_documents table...\n";

try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS generated_documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            booking_id BIGINT UNSIGNED NULL,
            payment_id BIGINT UNSIGNED NULL,
            document_type ENUM('sale_agreement','allotment_letter','receipt','registry_deed','noc','other') NOT NULL,
            template_used VARCHAR(100) DEFAULT 'standard',
            content LONGTEXT NOT NULL,
            status ENUM('draft','final','cancelled') DEFAULT 'draft',
            generated_by BIGINT UNSIGNED NULL,
            file_path VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_booking (booking_id),
            INDEX idx_payment (payment_id),
            INDEX idx_type (document_type),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✅ generated_documents table created\n";

    // Add foreign key constraints if tables exist
    $tables = ['plot_bookings', 'payments'];
    foreach ($tables as $table) {
        $exists = $db->fetch("SHOW TABLES LIKE '$table'");
        if ($exists) {
            try {
                $fkColumn = $table === 'plot_bookings' ? 'booking_id' : 'payment_id';
                $refTable = $table === 'plot_bookings' ? 'plot_bookings' : 'payments';
                $refColumn = $table === 'plot_bookings' ? 'id' : 'id';
                
                $db->exec("
                    ALTER TABLE generated_documents 
                    ADD CONSTRAINT fk_generated_docs_$table 
                    FOREIGN KEY ($fkColumn) REFERENCES $refTable($refColumn) ON DELETE SET NULL
                ");
                echo "✅ Foreign key added for $table\n";
            } catch (\Throwable $e) {
                echo "⚠️ Foreign key for $table skipped: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✅ Migration completed successfully!\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}