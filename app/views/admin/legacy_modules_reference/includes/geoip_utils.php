<?php
function lookup_ip_geolocation($ip) {
    $url = "http://ip-api.com/json/" . urlencode($ip);
    $resp = @file_get_contents($url);
    if ($resp === false) return [];
    $data = json_decode($resp, true);
    if (!is_array($data) || $data['status'] !== 'success') return [];
    return [
        'city' => $data['city'] ?? '',
        'region' => $data['regionName'] ?? '',
        'country' => $data['country'] ?? '',
        'timezone' => $data['timezone'] ?? '',
        'lat' => $data['lat'] ?? '',
        'lon' => $data['lon'] ?? ''
    ];
}
?>
