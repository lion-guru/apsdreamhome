<?php
// Scratch DB check: test users + MLM data presence (read-only)
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$users = ['testuser@example.com','agent1@apsdreamhome.com','admin@apsdreamhome.com','testassociate@example.com'];
foreach ($users as $e) {
  $s = $pdo->prepare("SELECT id,name,email,role,status FROM users WHERE email=?");
  $s->execute([$e]);
  $r = $s->fetch(PDO::FETCH_ASSOC);
  echo $e . ' => ' . ($r ? json_encode($r) : 'NOT FOUND') . PHP_EOL;
}
// sponsor candidates: any associate with referral code
try {
  $rows = $pdo->query("SELECT user_id,referral_code,sponsor_code,`rank`,total_team_size,direct_referrals FROM mlm_profiles LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
  echo "mlm_profiles sample: " . count($rows) . PHP_EOL;
  foreach ($rows as $r) echo '  ' . json_encode($r) . PHP_EOL;
} catch (Throwable $t) { echo "mlm_profiles ERR: ".$t->getMessage().PHP_EOL; }
try {
  echo "mlm_network_tree rows: " . $pdo->query("SELECT COUNT(*) FROM mlm_network_tree")->fetchColumn() . PHP_EOL;
} catch (Throwable $t) { echo "mlm_network_tree ERR: ".$t->getMessage().PHP_EOL; }
try {
  echo "mlm_commission_ledger rows: " . $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn() . PHP_EOL;
  $s = $pdo->query("SELECT DISTINCT status FROM mlm_commission_ledger LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
  echo "ledger statuses: " . json_encode($s) . PHP_EOL;
} catch (Throwable $t) { echo "ledger ERR: ".$t->getMessage().PHP_EOL; }
try {
  echo "plots available: " . $pdo->query("SELECT COUNT(*) FROM plots WHERE status='available'")->fetchColumn() . PHP_EOL;
  echo "colonies active: " . $pdo->query("SELECT COUNT(*) FROM colonies")->fetchColumn() . PHP_EOL;
} catch (Throwable $t) { echo "plots ERR: ".$t->getMessage().PHP_EOL; }
