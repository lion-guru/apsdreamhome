<?php
header('Content-Type: text/plain');

$pem = "-----BEGIN PRIVATE KEY-----\nMIGHAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBG0wawIBAQQgNn8NCx0mDSDFGCQ4\ncpDgktLtg1MUTiAbd6vJ4lBIGNChRANCAAQ4rT2/Ud6cN1mNJcwpKvxRQuMS8j8Q\nUNsnCEkJi6/QnzCCenbMMmGlwU7csg8Hnl5xY+63ye9lprFv/ahFpIXJ\n-----END PRIVATE KEY-----\n";

// Strip PEM headers
$lines = explode("\n", $pem);
$base64 = '';
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '-----') === 0) continue;
    $base64 .= $line;
}
$der = base64_decode($base64);
echo "DER length: " . strlen($der) . "\n";
echo "DER hex: " . bin2hex($der) . "\n";

// Search for \x04\x20
$pos = strpos($der, "\x04\x20");
if ($pos !== false) {
    $rawPriv = substr($der, $pos + 2, 32);
    echo "Found raw private key: " . bin2hex($rawPriv) . " (length: " . strlen($rawPriv) . ")\n";
    
    // Check if this matches Vapid Private Key in decodeVapidKey (test_push_sender.php line 59 has:
    // $priv = 'YTiliiKyMyrQwO2cx7Wc_3Pbg-xbjUN0usT0LQnjKo4';
    // $dpriv = PushSender::b64UrlDecode($priv);
    // )
} else {
    echo "Could not find \\x04\\x20 in DER!\n";
}
