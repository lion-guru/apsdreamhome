<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$row = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'")->fetch();
echo "ENUM: " . $row['Type'] . "\n\n";

// Also verify current_level values
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level")->fetchAll();
echo "mlm_profiles.current_level:\n";
foreach ($rows as $r) echo "  '" . $r['current_level'] . "' = " . $r['cnt'] . "\n";

// Check network_tree unique index
$idx = $pdo->query("SHOW INDEX FROM mlm_network_tree WHERE Column_name = 'associate_id' AND Non_unique = 0")->fetchAll();
echo "\nnetwork_tree unique index on associate_id: " . (count($idx) > 0 ? "YES" : "NO") . "\n";

// Verify new columns
$cols = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger")->fetchAll(PDO::FETCH_COLUMN);
echo "\nmlm_commission_ledger columns:\n";
foreach ($cols as $c) echo "  $c\n";
