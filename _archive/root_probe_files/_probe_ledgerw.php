<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT=>10]);
$pdo->exec("SET innodb_lock_wait_timeout=5");
$t0 = microtime(true);
try {
  $pdo->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id,booking_id,commission_type,amount,status,tenant_id,created_at) VALUES (2,NULL,'direct_sale',5000.00,'pending',1,NOW())")->execute();
  $id = (int)$pdo->lastInsertId();
  echo "INSERT ok id=$id in ".round(microtime(true)-$t0,2)."s".PHP_EOL;
  $pdo->prepare("UPDATE mlm_commission_ledger SET status='approved' WHERE id=?")->execute([$id]);
  echo "UPDATE ok".PHP_EOL;
  $pdo->prepare("DELETE FROM mlm_commission_ledger WHERE id=?")->execute([$id]);
  echo "DELETE ok, left=". $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger WHERE id=$id")->fetchColumn() .PHP_EOL;
} catch (Throwable $e) { echo "ERR after ".round(microtime(true)-$t0,2)."s: ".$e->getMessage().PHP_EOL; }
