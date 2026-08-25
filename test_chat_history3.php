<?php
// First login
$ch = curl_init('http://localhost/apsdreamhome/auth/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=testuser@example.com&password=Aps@2026');
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies2.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$response = curl_exec($ch);
curl_close($ch);

// Now test chat history
$ch = curl_init('http://localhost/apsdreamhome/api/v2/mobile/chat/history?session_id=87');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies2.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo $response . "\n";
curl_close($ch);