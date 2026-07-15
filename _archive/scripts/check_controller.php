<?php
$f = 'C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Marketing/MarketingAutomationController.php';
$c = file_get_contents($f);
echo 'authService: ' . substr_count($c, 'authService') . "\n";
echo 'viewRenderer: ' . substr_count($c, 'viewRenderer') . "\n";
echo 'AuthenticationService: ' . substr_count($c, 'AuthenticationService') . "\n";
echo 'ViewRenderer: ' . substr_count($c, 'ViewRenderer') . "\n";
echo 'skipCsrfProtection: ' . substr_count($c, 'skipCsrfProtection') . "\n";
echo 'requireAdmin: ' . substr_count($c, 'requireAdmin') . "\n";
echo 'render: ' . substr_count($c, '->render(') . "\n";