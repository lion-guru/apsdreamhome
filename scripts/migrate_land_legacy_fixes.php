<?php
/**
 * Land legacy fixes — Session 80
 * - Archive colony_phases (0 rows, 0 refs, superseded by plots.phase)
 * - Create land_transactions (missing table for LandController:241,520)
 * Idempotent.
 */
require_once __DIR__ . '/../config/bootstrap.php';
$pdo = App\Core\Database\Database::getInstance()->getConnection();
try{ $pdo->exec("RENAME TABLE colony_phases TO _archive_colony_phases"); echo "OK colony_phases archived\n"; }catch(Throwable $e){
  if(strpos($e->getMessage(),'_archive_colony_phases')!==false) echo "OK colony_phases already archived\n"; else echo "SKIP colony_phases: ".$e->getMessage()."\n";
}
$pdo->exec("CREATE TABLE IF NOT EXISTS land_transactions (id INT AUTO_INCREMENT PRIMARY KEY, tenant_id INT UNSIGNED NOT NULL DEFAULT 1, land_id INT NOT NULL, transaction_type ENUM('purchase','sale','development_cost','maintenance','other') NOT NULL DEFAULT 'other', amount DECIMAL(15,2) NOT NULL DEFAULT 0.00, description TEXT NULL, transaction_date DATE NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY idx_lt_land (land_id), KEY idx_lt_tenant (tenant_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK land_transactions\n";
echo "Done.\n";
