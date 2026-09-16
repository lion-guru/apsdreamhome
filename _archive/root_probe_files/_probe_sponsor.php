<?php
require_once __DIR__ . '/config/bootstrap.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$r = $pdo->query("SELECT id,name,email,role,referral_code,referred_by,status,registration_status FROM users WHERE id IN (1,2,49,53)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo json_encode($row).PHP_EOL;
echo "--- validate AGENT2 ---".PHP_EOL;
$s = new \App\Services\ReferralService();
var_dump($s->validateUserReferralCode('AGENT2'));
echo "--- agent1 own code validate ---".PHP_EOL;
$code = $pdo->query("SELECT referral_code FROM users WHERE id=2")->fetchColumn();
echo "users.referral_code for agent1 = [$code]".PHP_EOL;
var_dump($s->validateUserReferralCode((string)$code));
