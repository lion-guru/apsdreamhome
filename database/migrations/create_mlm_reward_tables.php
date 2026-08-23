<?php
// Create missing MLM rewards tables + seed rank criteria
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '2jcePXuNaOfEyo6I5wJVkG', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS mlm_rank_criteria (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
  `rank` VARCHAR(30) NOT NULL,
  min_monthly_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_team_size INT UNSIGNED NOT NULL DEFAULT 0,
  min_active_downlines INT UNSIGNED NOT NULL DEFAULT 0,
  min_monthly_commission DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tenant_rank (tenant_id, `rank`),
  KEY idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("INSERT IGNORE INTO mlm_rank_criteria (`rank`, min_monthly_sales, min_team_size, min_active_downlines, min_monthly_commission) VALUES
('bronze', 0, 0, 0, 0),
('silver', 50000, 5, 3, 2000),
('gold', 150000, 15, 10, 8000),
('platinum', 400000, 35, 25, 25000),
('diamond', 1000000, 80, 50, 75000),
('crown', 2500000, 180, 120, 200000)");

$pdo->exec("CREATE TABLE IF NOT EXISTS mlm_rank_upgrades (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
  associate_id INT UNSIGNED NOT NULL,
  old_rank VARCHAR(30) NOT NULL DEFAULT '',
  new_rank VARCHAR(30) NOT NULL DEFAULT '',
  upgrade_date DATE NULL,
  remarks VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tenant (tenant_id),
  KEY idx_associate (associate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "criteria rows: " . $pdo->query("SELECT COUNT(*) FROM mlm_rank_criteria")->fetchColumn() . "\n";
foreach ($pdo->query("SELECT `rank`, min_monthly_sales FROM mlm_rank_criteria") as $r) {
    echo "  {$r['rank']}: {$r['min_monthly_sales']}\n";
}
echo "upgrades table OK\n";
