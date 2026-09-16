<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT=>5]);
echo "scratch pending 5000/6000: " . $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE amount IN (5000.00,6000.00) AND status='pending' AND beneficiary_user_id=2")->fetchColumn() . PHP_EOL;
echo "processlist:".PHP_EOL;
foreach ($pdo->query("SHOW FULL PROCESSLIST")->fetchAll(PDO::FETCH_ASSOC) as $p) {
  echo "  {$p['Id']} {$p['User']} {$p['Command']} {$p['Time']}s {$p['State']} " . substr((string)$p['Info'],0,80) . PHP_EOL;
}
echo "ledger count: " . $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn() . PHP_EOL;
