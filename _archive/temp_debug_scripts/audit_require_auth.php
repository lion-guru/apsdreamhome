<?php
/**
 * Audit: Find admin controller methods missing requireAdmin() or requireLogin().
 *
 * Scans all PHP files under app/Http/Controllers/Admin/ for public methods
 * that do NOT call requireAdmin() or requireLogin() in their first 10 lines.
 * Reports methods that are potentially unprotected.
 *
 * Usage: php testing/audit_require_auth.php
 */

$root = dirname(__DIR__);
$adminDir = $root . '/app/Http/Controllers/Admin';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminDir));
$methods = 0;
$protected = 0;
$unprotected = 0;
$issues = [];

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $relPath = str_replace($root . '\\', '', $file->getPathname());
    $content = file_get_contents($file->getPathname());

    // Find all public methods
    preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE);
    
    foreach ($matches[1] as [$methodName, $offset]) {
        // Skip magic methods
        if (in_array($methodName, ['__construct', '__destruct', '__get', '__set', '__call'])) continue;
        
        $methods++;
        
        // Get the method body (next ~20 lines)
        $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;
        $lines = explode("\n", substr($content, $offset));
        $methodBody = implode("\n", array_slice($lines, 0, 20));
        
        // Check for auth calls
        $hasAuth = (
            strpos($methodBody, 'requireAdmin()') !== false ||
            strpos($methodBody, 'requireLogin()') !== false ||
            strpos($methodBody, 'requireAuth()') !== false ||
            strpos($methodBody, 'checkAuth()') !== false ||
            strpos($methodBody, 'requireRole(') !== false
        );
        
        // Also check if parent constructor calls requireAdmin (class-level)
        if (!$hasAuth) {
            // Check if the class constructor has requireAdmin
            $classMatch = [];
            preg_match('/class\s+\w+[^{]*\{/', $content, $classMatch, PREG_OFFSET_CAPTURE);
            if ($classMatch) {
                $classStart = $classMatch[0][1];
                $constructorBody = substr($content, $classStart, 500);
                $hasAuth = (
                    strpos($constructorBody, 'requireAdmin()') !== false ||
                    strpos($constructorBody, 'requireLogin()') !== false
                );
            }
        }
        
        if ($hasAuth) {
            $protected++;
        } else {
            $unprotected++;
            $issues[] = "$relPath:$lineNum $methodName()";
        }
    }
}

echo "=== Admin Controller Auth Audit ===\n\n";
echo "Total public methods: $methods\n";
echo "Protected (has requireAdmin/requireLogin): $protected\n";
echo "UNPROTECTED: $unprotected\n\n";

if ($unprotected > 0) {
    echo "--- Unprotected Methods ---\n";
    foreach ($issues as $issue) {
        echo "  $issue\n";
    }
} else {
    echo "All methods are protected!\n";
}?>