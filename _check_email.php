<?php
$c = file_get_contents('app/Services/Communication/EmailService.php');
preg_match_all('/public\s+function\s+(\w+)\s*\(/', $c, $methods);
echo "EmailService methods:\n";
foreach ($methods[1] as $m) echo "  $m()\n";

echo "\n--- Has sendPasswordResetEmail? ---\n";
echo strpos($c, 'sendPasswordResetEmail') !== false ? "YES\n" : "NO\n";
echo strpos($c, 'sendResetPassword') !== false ? "YES\n" : "NO\n";
echo strpos($c, 'sendEmail') !== false ? "YES\n" : "NO\n";
