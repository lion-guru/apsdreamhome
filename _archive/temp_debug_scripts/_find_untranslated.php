<?php
/**
 * Quick script to find identical en/hi pairs.
 * Run via: php testing/_find_untranslated.php
 */
define('APP_ROOT', dirname(__DIR__));

$allowedIdentical = [
    'no_bedrooms', 'one_bhk', 'two_bhk', 'three_bhk', 'four_bhk',
    'currency_inr',
    'google_login', 'facebook_login', 'linkedin_login',
    'upi_id', 'phone_ph', 'email_ph', 'name_ph', 'password_ph',
    'british_english', 'american_english', 'english_lang', 'hindi_lang',
    'admin_hash_label',
];

function flatten(array $arr, string $prefix = ''): array {
    $out = [];
    foreach ($arr as $k => $v) {
        $key = $prefix === '' ? (string)$k : $prefix . '.' . $k;
        if (is_array($v)) {
            $out = array_merge($out, flatten($v, $key));
        } else {
            $out[$key] = $v;
        }
    }
    return $out;
}

$en = flatten(require APP_ROOT . '/lang/en.php');
$hi = flatten(require APP_ROOT . '/lang/hi.php');

$identical = [];
foreach ($en as $k => $v) {
    if (isset($hi[$k]) && $hi[$k] === $v && !in_array($k, $allowedIdentical, true)) {
        $identical[$k] = $v;
    }
}

echo "Found " . count($identical) . " identical en/hi pairs:\n\n";
foreach ($identical as $k => $v) {
    echo "KEY:    $k\n";
    echo "VALUE:  $v\n\n";
}?>