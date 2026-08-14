<?php
$f = 'C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Marketing/MarketingAutomationController.php';
$c = file_get_contents($f);

// Remove unused imports
$c = str_replace("use App\Services\Auth\AuthenticationService;\n", '', $c);
$c = str_replace("use App\Core\ViewRenderer;\n", '', $c);

// Remove properties
$c = preg_replace('/\s*private \$authService;\n/', "\n", $c);
$c = preg_replace('/\s*private \$viewRenderer;\n/', "\n", $c);

// Remove constructor lines
$c = str_replace("\$this->authService = new AuthenticationService();\n", '', $c);
$c = str_replace("\$this->viewRenderer = new ViewRenderer();\n", '', $c);

// Remove skipCsrfProtection block
$c = preg_replace(
    '/    \/\*\*+\s*\n\s*\* This controller validates CSRF.*?protected function skipCsrfProtection\(\): bool\s*\n\s*\{\s*\n\s*return true;\s*\n\s*\n\s*\}/s',
    '',
    $c
);

// Replace auth checks with requireAdmin (pattern with redirect)
$c = preg_replace(
    '/if \(!\$this->authService->isAuthenticated\(\)\) \{\s*\n\s*\$_SESSION\[\'errors\'\] = \[.*?\];\s*\n\s*\$this->redirect\(.*?\);\s*\n\s*return;\s*\n\s*\}/',
    '$this->requireAdmin();',
    $c
);

// Pattern: if auth check returning array
$c = preg_replace(
    '/if \(!\$this->authService->isAuthenticated\(\)\) \{\s*\n\s*return \[\s*\n\s*\'success\' => false,\s*\n\s*\'message\' => \'Access denied\'\s*\n\s*\];\s*\n\s*\}/',
    '$this->requireAdmin();',
    $c
);

// Pattern: if auth check with permission returning array
$c = preg_replace(
    '/if \(!\$this->authService->isAuthenticated\(\) \|\| .*?\) \{\s*\n\s*return \[\s*\n\s*\'success\' => false,\s*\n\s*\'message\' => \'Access denied\'\s*\n\s*\];\s*\n\s*\}/s',
    '$this->requireAdmin();',
    $c
);

// Remove user key from data arrays
$c = preg_replace("/\n\s*'user' => \$this->authService->getCurrentUser\(\),\n/", "\n", $c);

// Replace viewRenderer->render with render
$c = str_replace('return $this->viewRenderer->render(', '$this->render(', $c);
$c = str_replace('$this->viewRenderer->render(', '$this->render(', $c);

// Clean up multiple blank lines
$c = preg_replace('/\n{3,}/', "\n\n", $c);

file_put_contents($f, $c);
echo "Fixed MarketingAutomationController\n";?>