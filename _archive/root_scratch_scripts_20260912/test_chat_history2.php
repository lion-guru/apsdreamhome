<?php
$ch = curl_init('http://localhost/apsdreamhome/api/v2/mobile/chat/history?session_id=87&user_id=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo $response . "\n";
curl_close($ch);