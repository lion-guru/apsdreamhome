<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
echo "--- user_wallets user 2 ---".PHP_EOL;
try { echo json_encode($pdo->query("SELECT * FROM user_wallets WHERE user_id=2")->fetch(PDO::FETCH_ASSOC)).PHP_EOL; }
catch (Throwable $e) { echo "ERR ".$e->getMessage().PHP_EOL; }
echo "--- wallet_points user 2 ---".PHP_EOL;
try { echo json_encode($pdo->query("SELECT user_id,points_balance,total_earned,commission_earnings FROM wallet_points WHERE user_id=2")->fetch(PDO::FETCH_ASSOC)).PHP_EOL; }
catch (Throwable $e) { echo "ERR ".$e->getMessage().PHP_EOL; }
echo "--- referral_signup rows beneficiary 2 ---".PHP_EOL;
foreach ($pdo->query("SELECT id,source_user_id,amount,status,created_at FROM mlm_commission_ledger WHERE beneficiary_user_id=2 AND commission_type='referral_signup' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC) as $r) echo json_encode($r).PHP_EOL;
