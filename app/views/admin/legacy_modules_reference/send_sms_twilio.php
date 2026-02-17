<?php
// Send SMS via Twilio API
// Usage: require and call send_sms_twilio($msg, $cfg)
function send_sms_twilio($msg, $cfg) {
    if (empty($cfg['twilio_enabled']) || empty($cfg['twilio_sid']) || empty($cfg['twilio_token']) || empty($cfg['twilio_from']) || empty($cfg['twilio_to'])) return;
    $sid = $cfg['twilio_sid'];
    $token = $cfg['twilio_token'];
    $from = $cfg['twilio_from'];
    $to = $cfg['twilio_to'];
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = http_build_query([
        'From' => $from,
        'To' => $to,
        'Body' => $msg
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code < 200 || $http_code >= 300) {
        error_log("Twilio SMS send failed: $response");
    }
}
