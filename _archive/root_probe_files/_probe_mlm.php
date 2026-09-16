<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach (['mlm_profiles','mlm_network_tree'] as $t) {
  echo "=== $t ===" . PHP_EOL;
  foreach ($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  {$c['Field']} {$c['Type']} {$c['Null']} {$c['Key']} {$c['Default']}" . PHP_EOL;
  }
}
echo "=== agent1 profile ===" . PHP_EOL;
$r = $pdo->query("SELECT * FROM mlm_profiles WHERE user_id=2")->fetch(PDO::FETCH_ASSOC);
echo json_encode($r, JSON_PRETTY_PRINT) . PHP_EOL;
echo "=== agent1 tree node ===" . PHP_EOL;
$r = $pdo->query("SELECT * FROM mlm_network_tree WHERE associate_id=2")->fetch(PDO::FETCH_ASSOC);
echo json_encode($r, JSON_PRETTY_PRINT) . PHP_EOL;
echo "=== agent1 downline count ===" . PHP_EOL;
echo "children: " . $pdo->query("SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id=2 OR sponsor_id=2")->fetchColumn() . PHP_EOL;
