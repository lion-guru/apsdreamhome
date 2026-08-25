<?php
$ch = curl_init('http://localhost/apsdreamhome/api/v2/mobile/chat/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "token" => "67fad15b1bb08861f12107b79d3baaf4",
    "message" => "Hello, this is a test message"
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo $response . "\n";
curl_close($ch);