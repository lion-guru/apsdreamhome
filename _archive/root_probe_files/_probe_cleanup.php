<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
echo "--- wallet_transactions cols ---".PHP_EOL;
foreach ($pdo->query("DESCRIBE wallet_transactions")->fetchAll(PDO::FETCH_ASSOC) as $c) echo "  {$c['Field']} {$c['Type']}".PHP_EOL;
$rows = $pdo->query("SELECT id,name,email,phone,role,registration_status FROM users WHERE email LIKE 'probe%'")->fetchAll(PDO::FETCH_ASSOC);
echo "leftover probe users: ".count($rows).PHP_EOL;
foreach ($rows as $r) {
  echo json_encode($r).PHP_EOL;
  $uid = (int)$r['id'];
  $pdo->prepare("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id=? OR source_user_id=?")->execute([$uid,$uid]);
  foreach (['network_tree'=>'associate_id','mlm_network_tree'=>'associate_id','mlm_profiles'=>'user_id','associates'=>'user_id','user_wallets'=>'user_id','wallet_transactions'=>'user_id','customer_referrals'=>'referred_user_id','tenant_users'=>'user_id'] as $t=>$col) {
    try {
      $cols = array_column($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC),'Field');
      if (in_array($col,$cols,true)) $pdo->prepare("DELETE FROM `$t` WHERE `$col`=?")->execute([$uid]);
    } catch (Throwable $e) { echo "  $t: ".$e->getMessage().PHP_EOL; }
  }
  try { $pdo->prepare("DELETE FROM wallet_transactions WHERE reference_id=?")->execute([$uid]); } catch (Throwable $e) {}
  try { $pdo->prepare("DELETE FROM customer_referrals WHERE referrer_user_id=?")->execute([$uid]); } catch (Throwable $e) {}
  $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
}
echo "remaining probe users: ".$pdo->query("SELECT COUNT(*) FROM users WHERE email LIKE 'probe%'")->fetchColumn().PHP_EOL;
echo "agent1 counters: ".json_encode($pdo->query("SELECT direct_referrals,total_team_size FROM mlm_profiles WHERE user_id=2")->fetch(PDO::FETCH_ASSOC)).PHP_EOL;
echo "ledger count: ".$pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn().PHP_EOL;
