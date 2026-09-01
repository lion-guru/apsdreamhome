<?php
// API smoke — hit 10 public GET endpoints without auth (expect 200/401, not 500)
$base = 'http://localhost/apsdreamhome';
$routes = json_decode(file_get_contents(__DIR__.'/api_routes.json'), true);
$sample = array_slice(array_filter($routes, fn($r)=>str_starts_with($r,'/api/')), 0, 20);
$pass=0; $fail=0;
foreach($sample as $path){
  $url = $base . $path;
  $ch = curl_init($url);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>5, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_HEADER=>false]);
  $body = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $ok = in_array($code, [200,302,401,403,405,422]); // 500 is fail
  echo ($ok?'PASS':'FAIL')." $path => $code\n";
  if($ok) $pass++; else $fail++;
}
echo "\nRESULT: $pass PASS / $fail FAIL (sample 20)\n";
exit($fail?1:0);
