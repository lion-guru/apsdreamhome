<?php
/**
 * Generate VAPID keys for Web Push notifications.
 * Run: php scripts/generate_vapid_keys.php
 */
$keyDir = dirname(__DIR__) . '/config';
$privateKeyFile = $keyDir . '/vapid_private.key';
$publicKeyFile = $keyDir . '/vapid_public.key';

if (file_exists($privateKeyFile)) {
    echo "VAPID keys already exist. Delete files to regenerate.\n";
    exit(0);
}

if (!function_exists('openssl_pkey_new')) {
    echo "[ERROR] openssl extension required.\n";
    exit(1);
}

$privateKey = openssl_pkey_new([
    'curve_name' => 'prime256v1',
    'private_key_type' => OPENSSL_KEYTYPE_EC,
]);

if (!$privateKey) {
    echo "[ERROR] Failed to generate key.\n";
    exit(1);
}

openssl_pkey_export($privateKey, $privateKeyPem);
file_put_contents($privateKeyFile, $privateKeyPem);
chmod($privateKeyFile, 0600);

$details = openssl_pkey_get_details($privateKey);
$x = strtr(rtrim(base64_encode($details['ec']['x']), '='), '+/', '-_');
$y = strtr(rtrim(base64_encode($details['ec']['y']), '='), '+/', '-_');
$publicKey = $x . '.' . $y;
file_put_contents($publicKeyFile, $publicKey);

echo "VAPID keys generated:\n";
echo "  Private: {$privateKeyFile}\n";
echo "  Public:  {$publicKeyFile}\n\n";
echo "Add to .env:\n";
echo "  VAPID_PUBLIC_KEY={$publicKey}\n";
