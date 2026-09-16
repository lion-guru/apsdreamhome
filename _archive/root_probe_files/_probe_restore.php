<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->prepare("UPDATE mlm_profiles SET direct_referrals=3, total_team_size=9, updated_at=NOW() WHERE user_id=2")->execute();
echo "counters: ".json_encode($pdo->query("SELECT direct_referrals,total_team_size FROM mlm_profiles WHERE user_id=2")->fetch(PDO::FETCH_ASSOC)).PHP_EOL;
$pdo->prepare("UPDATE wallet_points SET points_balance=GREATEST(points_balance-200,0), total_earned=GREATEST(total_earned-200,0) WHERE user_id=2")->execute();
echo "wallet: ".json_encode($pdo->query("SELECT points_balance,total_earned FROM wallet_points WHERE user_id=2")->fetch(PDO::FETCH_ASSOC)).PHP_EOL;
echo "probe users left: ".$pdo->query("SELECT COUNT(*) FROM users WHERE email LIKE 'probe%'")->fetchColumn().PHP_EOL;
echo "ledger: ".$pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn().PHP_EOL;
