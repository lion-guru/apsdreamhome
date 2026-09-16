<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach ($pdo->query("DESCRIBE mlm_commission_ledger")->fetchAll(PDO::FETCH_ASSOC) as $c) {
  echo "{$c['Field']} | {$c['Type']} | null={$c['Null']} | key={$c['Key']} | default=" . var_export($c['Default'], true) . " | extra={$c['Extra']}" . PHP_EOL;
}
echo "--- sample row ---".PHP_EOL;
echo json_encode($pdo->query("SELECT * FROM mlm_commission_ledger WHERE status='pending' LIMIT 1")->fetch(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT).PHP_EOL;
