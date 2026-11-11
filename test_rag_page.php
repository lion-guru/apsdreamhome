<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/admin/login?test_login=2');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/temp/test_rag.jar');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/temp/test_rag.jar');
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Login: HTTP $code\n";
curl_close($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/admin/rag');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/temp/test_rag.jar');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/temp/test_rag.jar');
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "RAG Admin: HTTP $code\n";
echo substr($body, 0, 500) . "\n";
curl_close($ch);