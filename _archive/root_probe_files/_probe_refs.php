<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach ($pdo->query("SELECT id,beneficiary_user_id,source_user_id,commission_type,amount,status,created_at FROM mlm_commission_ledger WHERE beneficiary_user_id=121190 OR source_user_id=121190")->fetchAll(PDO::FETCH_ASSOC) as $r) echo json_encode($r).PHP_EOL;
echo "--- wallet txns ref 121190 ---".PHP_EOL;
try {
foreach ($pdo->query("SELECT id,user_id,type,amount,reference_id,created_at FROM wallet_transactions WHERE user_id=121190 OR reference_id=121190")->fetchAll(PDO::FETCH_ASSOC) as $r) echo json_encode($r).PHP_EOL;
} catch (Throwable $e) { echo "wtx ERR: ".$e->getMessage().PHP_EOL; }
