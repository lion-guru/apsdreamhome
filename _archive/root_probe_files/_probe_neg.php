<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach ($pdo->query("SELECT id,commission_type,amount,status,notes,created_at FROM mlm_commission_ledger WHERE beneficiary_user_id=2 AND status='approved'")->fetchAll(PDO::FETCH_ASSOC) as $r) {
  echo json_encode($r).PHP_EOL;
}
