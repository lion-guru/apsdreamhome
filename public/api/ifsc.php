<?php
/**
 * IFSC API Service - APS Dream Homes
 * Migrated from resources/views/Views/ifsc_api.php
 * Returns JSON response for IFSC lookups
 */

header('Content-Type: application/json');

$ifsc_code = isset($_GET['ifsc']) ? strtoupper(trim($_GET['ifsc'])) : (isset($_POST['ifsc']) ? strtoupper(trim($_POST['ifsc'])) : '');

if (empty($ifsc_code)) {
    echo json_encode([
        'success' => false,
        'message' => 'IFSC code is required'
    ]);
    exit();
}

// Basic IFSC format validation (optional but good)
if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc_code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid IFSC code format'
    ]);
    exit();
}

$url = 'https://bank-apis.justinclicks.com/API/V1/IFSC/' . $ifsc_code . '/';

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL Error: ' . $err
    ]);
} else {
    $data = json_decode($response, true);
    if (isset($data['IFSC'])) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'IFSC details not found'
        ]);
    }
}
