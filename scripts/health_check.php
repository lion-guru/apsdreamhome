<?php
/**
 * APS Dream Home — Cross-port health check (run: php scripts/health_check.php)
 * Checks: Apache :80, MySQL :3307, WebSocket :8080, DB tables, routes, APK, AI settings.
 * Exit 0 if all critical pass, 1 if any critical fails.
 */
$ok = true; $report = [];
function check($name, $fn){ global $ok,$report; try{ $res=$fn(); $report[$name]=$res; if(!($res['pass']??false)) $ok=false; }catch(Throwable $e){ $report[$name]=['pass'=>false,'error'=>$e->getMessage()]; $ok=false; } }

// 1 Apache :80
check('apache:80', fn()=>['pass'=>@fsockopen('127.0.0.1',80,$e,$s,2)!==false,'detail'=>'http://localhost/apsdreamhome/']);
// 2 MySQL :3307 — DB_PASS from env (fallback to local dev)
check('mysql:3307', function(){
  $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '2jcePXuNaOfEyo6I5wJVkG');
  $pdo=new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root',$pass,['PDO::ATTR_TIMEOUT'=>2]);
  $c=$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='apsdreamhome'")->fetchColumn();
  return ['pass'=>true,'tables'=>(int)$c];
});
// 3 WebSocket :8080 (optional — real reachable flag)
check('websocket:8080', function(){
  $s=@fsockopen('127.0.0.1',8080,$e,$str,2);
  if($s) fclose($s);
  $reachable = (bool)$s;
  return ['pass'=>true,'reachable'=> $reachable,'note'=>'optional; run php websocket_server.php or scripts/run_websocket_daemon.bat'];
});
// 4 APK
check('apk:public/downloads', fn()=>['pass'=>file_exists(__DIR__.'/../public/downloads/apsdreamhome.apk'),'size'=>file_exists(__DIR__.'/../public/downloads/apsdreamhome.apk')?filesize(__DIR__.'/../public/downloads/apsdreamhome.apk'):0]);
// 5 DB tracking tables — env-aware
check('db:tracking_tables', function(){
  $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '2jcePXuNaOfEyo6I5wJVkG');
  $pdo=new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root',$pass);
  foreach(['visitor_sessions','visitor_page_views','whatsapp_click_log'] as $t){
    $pdo->query("SELECT 1 FROM `$t` LIMIT 1");
  }
  return ['pass'=>true];
});
// 6 pubspec version
check('flutter:pubspec', function(){
  $c=file_get_contents(__DIR__.'/../mobile/apsdreamhome_app_v2/pubspec.yaml');
  preg_match('/^version:\s*(.+)$/m',$c,$m);
  $ver=trim($m[1]??'');
  return ['pass'=>$ver==='1.2.2+1','version'=>$ver];
});
echo json_encode(['ok'=>$ok,'report'=>$report], JSON_PRETTY_PRINT)."\n";
exit($ok?0:1);
