<?php
// scripts/security-audit.php

echo "Starting security audit...\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    echo "⚠️  Warning: PHP version is outdated. Please upgrade to PHP 7.4 or later.\n";
}

// Check for disabled functions
$requiredDisabled = ['exec', 'shell_exec', 'system', 'passthru'];
$disabled = array_intersect($requiredDisabled, array_map('trim', explode(',', ini_get('disable_functions'))));

if (count($disabled) !== count($requiredDisabled)) {
    echo "⚠️  Warning: The following dangerous functions are not disabled: " . implode(', ', array_diff($requiredDisabled, $disabled)) . "\n";
}

// Check file permissions
$files = [
    '.env' => 0640,
    'config/' => 0750,
    'storage/' => 0750,
    'bootstrap/cache/' => 0750
];

foreach ($files as $file => $expected) {
    if (file_exists($file)) {
        $perms = fileperms($file) & 0777;
        if ($perms !== $expected) {
            echo "⚠️  Warning: Incorrect permissions for $file: " . decoct($perms) . " (should be " . decoct($expected) . ")\n";
        }
    }
}

// Check for common vulnerabilities
$vulnerabilities = [
    'extract\(' => 'extract() function can lead to variable injection',
    'eval\(' => 'eval() is dangerous and should be avoided',
    'system\(' => 'system() can execute shell commands',
    'shell_exec\(' => 'shell_exec() can execute shell commands',
    'passthru\(' => 'passthru() can execute shell commands',
    '`.*`' => 'Backticks can execute shell commands',
    'create_function' => 'create_function() is deprecated and dangerous',
    'assert\(' => 'assert() can be used for code injection',
    'preg_replace\s*\(.*/e' => 'preg_replace() with /e modifier is dangerous',
    'include\s*\(\s*\$' => 'Dynamic includes can lead to file inclusion vulnerabilities',
    'require\s*\(\s*\$' => 'Dynamic requires can lead to file inclusion vulnerabilities'
];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../app'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        foreach ($vulnerabilities as $pattern => $message) {
            if (preg_match("/$pattern/", $content)) {
                echo "⚠️  Warning: $message in " . $file->getPathname() . "\n";
            }
        }
    }
}

echo "\n✅ Security audit completed.\n";
