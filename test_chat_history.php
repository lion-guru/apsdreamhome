<?php
$ch = curl_init('http://localhost/apsdreamhome/api/v2/mobile/chat/history?token=67fad15b1bb08861f12107b79d3baaf4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo $response . "\n";
curl_close($ch);