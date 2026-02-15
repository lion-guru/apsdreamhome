<?php
// Send a Slack notification to a channel (incoming webhook)
// Usage: send_slack_notification.php?text=Message+here
$webhook_url = 'https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ'; // Updated to real webhook
$text = isset($_GET['text']) ? $_GET['text'] : '';
if (!$text) {
    http_response_code(400); echo 'Missing text'; exit;
}
$payload = json_encode(['text' => $text]);
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($payload)]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($http_code !== 200) {
    http_response_code(500); echo 'Slack send failed'; exit;
}
echo 'OK';
