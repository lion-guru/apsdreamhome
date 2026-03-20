<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$apiKey = 'AIzaSyDfsQxz1ojlgOnlg4i_nFW7aUfYdQJTcxo'; // आपकी लेटेस्ट की
$url = 'https://generativelanguage.googleapis.com/v/models/gemini-pro:generateContent?key=' . $apiKey;

$data = [
    "contents" => [["parts" => [["text" => "Hello, is this API active?"]]]]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
echo "<pre>" . $response . "</pre>"; // यहाँ आपको असली एरर दिख जाएगा
curl_close($ch);
