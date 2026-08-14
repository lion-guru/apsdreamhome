<?php
$files = [
    __DIR__ . '/../app/Http/Controllers/Media/MediaLibraryController.php',
    __DIR__ . '/../app/Http/Controllers/Marketing/MarketingAutomationController.php',
    __DIR__ . '/../app/Http/Controllers/Land/PlottingController.php',
    __DIR__ . '/../app/Http/Controllers/Async/AsyncController.php',
    __DIR__ . '/../app/Http/Controllers/Business/AssociateController.php',
];

foreach ($files as $f) {
    $content = file_get_contents($f);
    $orig = $content;
    
    // Remove unused imports
    $content = str_replace("use App\\Services\\Auth\\AuthenticationService;\n", '', $content);
    $content = str_replace("use App\\Core\\ViewRenderer;\n", '', $content);
    
    // Remove properties
    $content = preg_replace('/\s*private \$authService;\n/', "\n", $content);
    $content = preg_replace('/\s*private \$viewRenderer;\n/', "\n", $content);
    
    // Remove constructor lines
    $content = str_replace("\$this->authService = new AuthenticationService();\n", '', $content);
    $content = str_replace("\$this->viewRenderer = new ViewRenderer();\n", '', $content);
    $content = str_replace("\$this->viewRenderer = new \\App\\Core\\ViewRenderer();\n", '', $content);
    
    // Remove skipCsrfProtection block
    $content = preg_replace(
        '/    \/\*\*+\s*\n\s*\* This controller validates CSRF.*?protected function skipCsrfProtection\(\): bool\s*\n\s*\{\s*\n\s*return true;\s*\n\s*\n\s*\}/s',
        '',
        $content
    );
    
    // Pattern 1: if auth check with redirect
    $content = preg_replace(
        '/if \(!\$this->authService->isAuthenticated\(\)\) \{\s*\n\s*\$_SESSION\[\'errors\'\] = \[.*?\];\s*\n\s*\$this->redirect\(.*?\);\s*\n\s*return;\s*\n\s*\}/',
        '$this->requireAdmin();',
        $content
    );
    
    // Pattern 2: if auth check returning array
    $content = preg_replace(
        '/if \(!\$this->authService->isAuthenticated\(\)\) \{\s*\n\s*return \[\s*\n\s*\'success\' => false,\s*\n\s*\'message\' => \'Access denied\'\s*\n\s*\];\s*\n\s*\}/',
        '$this->requireAdmin();',
        $content
    );
    
    // Pattern 3: if auth check with permission returning array
    $content = preg_replace(
        '/if \(!\$this->authService->isAuthenticated\(\) \|\| .*?\) \{\s*\n\s*return \[\s*\n\s*\'success\' => false,\s*\n\s*\'message\' => \'Access denied\'\s*\n\s*\];\s*\n\s*\}/s',
        '$this->requireAdmin();',
        $content
    );
    
    // Remove user key from data arrays
    $content = preg_replace('/\n\s*\'user\' => \$this->authService->getCurrentUser\(\),\n/', "\n", $content);
    
    // Replace viewRenderer->render with render
    $content = str_replace('return $this->viewRenderer->render(', '$this->render(', $content);
    
    // Clean up multiple blank lines
    $content = preg_replace('/\n{3,}/', "\n\n", $content);
    
    file_put_contents($f, $content);
    $changed = ($orig !== $content) ? 'CHANGED' : 'UNCHANGED';
    echo "$changed: " . basename($f) . "\n";
}
echo "Done!\n";?>