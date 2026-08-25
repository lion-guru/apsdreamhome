<?php
$ch = curl_init('http://localhost/apsdreamhome/api/v2/mobile/chat/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "token" => "test",
    "message" => "test"
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
$response = curl_exec($ch);
echo 'Error: ' . curl_error($ch) . "\n";
echo 'HTTP Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $response . "\n";
curl_close($ch);