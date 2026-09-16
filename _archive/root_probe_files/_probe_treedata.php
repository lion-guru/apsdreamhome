<?php
// Where does /api/mlm/tree-data redirect to, and why?
$jar = sys_get_temp_dir() . '/mission_assoc3.txt'; // reuse associate session jar if present
function req($method, $url, $jar, $form = null) {
  $ch = curl_init('http://localhost/apsdreamhome' . $url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  if ($form !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
  curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  curl_setopt($ch, CURLOPT_HEADER, true);
  $raw = (string)curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  curl_close($ch);
  return [$code, substr($raw, 0, $hsize), substr($raw, $hsize)];
}
echo "jar exists: " . (file_exists($jar) ? 'yes' : 'NO') . PHP_EOL;
[$c, $h, $b] = req('GET', '/api/mlm/tree-data?root_id=2&levels=2', $jar);
echo "assoc jar tree-data: http=$c len=" . strlen($b) . PHP_EOL;
foreach (explode("\n", $h) as $l) if (stripos($l, 'Location') === 0) echo "  $l";
echo "body head: " . substr($b, 0, 200) . PHP_EOL;
// fresh admin login then try
$jad = sys_get_temp_dir() . '/dbg_admin.txt'; @unlink($jad);
[$c, $h, $b] = req('GET', '/admin/login', $jad);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $b, $m);
[$c, $h, $b] = req('POST', '/admin/login', $jad, ['username' => 'admin@apsdreamhome.com', 'password' => 'Aps@2026', 'csrf_token' => $m[1] ?? '']);
echo "admin login: http=$c" . PHP_EOL;
[$c, $h, $b] = req('GET', '/api/mlm/tree-data?root_id=2&levels=2', $jad);
echo "admin jar tree-data: http=$c len=" . strlen($b) . PHP_EOL;
foreach (explode("\n", $h) as $l) if (stripos($l, 'Location') === 0) echo "  $l";
echo "body head: " . substr($b, 0, 200) . PHP_EOL;
// login_attempts state for testuser (throttleniosk)
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
echo "testuser recent failed attempts: " . $pdo->query("SELECT COUNT(*) FROM login_attempts WHERE identifier='testuser@example.com' AND success=0 AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)")->fetchColumn() . PHP_EOL;
