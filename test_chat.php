<?php
$ch = curl_init("http://localhost/apsdreamhome/api/v2/mobile/chat/send");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "token" => "1a3bbcf8e93ec3b5650ca44593dcd0c2",
    "message" => "Test message"
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Cookie: " . file_get_contents("C:/xampp/htdocs/apsdreamhome/cookies.txt")
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo curl_error($ch) . "\n";
echo $response . "\n";
curl_close($ch);