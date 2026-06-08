<?php
/**
 * Generate VAPID keys for Web Push notifications.
 * Run: php scripts/generate_vapid_keys.php
 *
 * Tries PHP openssl first; falls back to Node.js if openssl EC is broken.
 */
$keyDir = dirname(__DIR__) . '/config';
$privateKeyFile = $keyDir . '/vapid_private.key';
$publicKeyFile = $keyDir . '/vapid_public.key';

if (file_exists($privateKeyFile)) {
    echo "VAPID keys already exist at {$keyDir}/. Delete files to regenerate.\n";
    exit(0);
}

$generated = false;

// Try PHP openssl first
if (function_exists('openssl_pkey_new')) {
    $privateKey = @openssl_pkey_new([
        'curve_name' => 'prime256v1',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
    ]);

    if ($privateKey) {
        openssl_pkey_export($privateKey, $privateKeyPem);
        file_put_contents($privateKeyFile, $privateKeyPem);
        @chmod($privateKeyFile, 0600);

        $details = openssl_pkey_get_details($privateKey);
        $x = strtr(rtrim(base64_encode($details['ec']['x']), '='), '+/', '-_');
        $y = strtr(rtrim(base64_encode($details['ec']['y']), '='), '+/', '-_');
        $publicKey = $x . '.' . $y;
        file_put_contents($publicKeyFile, $publicKey);
        $generated = true;
        echo "Generated via PHP openssl (EC prime256v1).\n";
    }
}

// Fallback: Node.js crypto
if (!$generated && @exec('node --version', $out, $exitCode) === true && $exitCode === 0) {
    $nodeScript = <<<'NODEJS'
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');

const { privateKey, publicKey } = crypto.generateKeyPairSync('ec', { namedCurve: 'P-256' });
const rawPub = publicKey.export({ type: 'spki', format: 'der' });
const x = rawPub.slice(27, 59);
const y = rawPub.slice(59, 91);
const b64url = (buf) => buf.toString('base64').replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');

const configDir = path.resolve(process.argv[2]);
fs.writeFileSync(path.join(configDir, 'vapid_private.key'), privateKey.export({ type: 'pkcs8', format: 'pem' }));
fs.writeFileSync(path.join(configDir, 'vapid_public.key'), b64url(x) + '.' + b64url(y));
console.log(b64url(x) + '.' + b64url(y));
NODEJS;

    $tmpNode = tempnam(sys_get_temp_dir(), 'vapid_');
    file_put_contents($tmpNode, $nodeScript);
    $publicKey = trim(shell_exec("node " . escapeshellarg($tmpNode) . " " . escapeshellarg($keyDir)));
    @unlink($tmpNode);

    if ($publicKey && file_exists($privateKeyFile)) {
        @chmod($privateKeyFile, 0600);
        $generated = true;
        echo "Generated via Node.js crypto (P-256).\n";
    }
}

if (!$generated) {
    echo "[ERROR] Could not generate VAPID keys. Requires openssl EC or Node.js.\n";
    exit(1);
}

echo "VAPID keys generated:\n";
echo "  Private: {$privateKeyFile}\n";
echo "  Public:  {$publicKeyFile}\n\n";
echo "Add to .env:\n";
echo "  VAPID_PUBLIC_KEY={$publicKey}\n";
echo "  VAPID_PRIVATE_KEY=PEM:config/vapid_private.key\n";
