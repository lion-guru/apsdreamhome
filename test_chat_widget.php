<?php
$ch = curl_init('http://localhost/apsdreamhome/api/v2/mobile/chat/widget');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo $response . "\n";
curl_close($ch);