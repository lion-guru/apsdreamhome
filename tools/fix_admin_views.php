<?php
/**
 * fix_admin_views.php
 * 
 * Bulk-fixes common issues in admin view files for APS Dream Home.
 * - Removes session_start() calls (should be handled by controller)
 * - Fixes hardcoded header('Location: /admin/...') redirects (must use BASE_URL)
 * - Removes DOCTYPE/html/head/body wrappers (layout provides them)
 * - Removes redundant Bootstrap/FontAwesome CDN links
 *
 * SAFE: Creates .bak backups before modifying any file.
 */

// --- Configuration ---
$adminViewsDir = __DIR__ . '/../app/views/admin';
$logFile = __DIR__ . '/fix_admin_views_report.log';

// --- Stats ---
$stats = [
    'scanned' => 0,
    'modified' => 0,
    'errors' => 0,
    'fixes' => [
        'session_start_removed' => 0,
        'header_redirect_fixed' => 0,
        'header_redirect_hardcoded_fixed' => 0,
        'doctype_removed' => 0,
        'html_head_body_removed' => 0,
        'cdn_links_removed' => 0,
    ],
    'error_details' => [],
];

// --- Helpers ---

function logMsg($msg, $file = null) {
    global $logFile;
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] $msg" . ($file ? " | File: $file" : '');
    file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo $line . PHP_EOL;
}

function backupFile($path) {
    $backup = $path . '.bak';
    if (!file_exists($backup)) {
        copy($path, $backup);
    }
}

function fixSessionStart($content, &$fileStats) {
    $count = 0;
    
    // Pattern 1: @?@?session_start(); on its own line — preserve leading whitespace
    $content = preg_replace_callback(
        '/^(\s*)@?@?session_start\s*\(\s*\)\s*;\s*$/m',
        function ($m) {
            return $m[1] . '// Session started by controller';
        },
        $content, -1, $cnt
    );
    $count += $cnt;
    
    // Pattern 2: if (session_status() === PHP_SESSION_NONE) { @session_start(); } block (single line)
    $content = preg_replace_callback(
        '/^(\s*)if\s*\(\s*session_status\s*\(\s*\)\s*===\s*PHP_SESSION_NONE\s*\)\s*\{\s*@?session_start\s*\(\s*\)\s*;\s*\}/m',
        function ($m) {
            return $m[1] . '// Session started by controller';
        },
        $content, -1, $cnt
    );
    $count += $cnt;
    
    // Pattern 3: Multi-line session_start() guard (dashboard.php style) — preserve indentation
    $content = preg_replace_callback(
        '/\{\s*\n(\s+)@?session_start\s*\(\s*\)\s*;\s*\n(\s+)\}/m',
        function ($m) {
            return "{\n" . ($m[1] ?? '') . "// Session started by controller\n" . ($m[2] ?? '') . "}";
        },
        $content, -1, $cnt
    );
    $count += $cnt;
    
    if ($count > 0) {
        $fileStats['session_start_removed'] += $count;
    }
    
    return [$content, $count];
}

function fixHeaderRedirects($content, &$fileStats) {
    $count = 0;
    
    // All hardcoded admin redirects: replace with BASE_URL version
    // Match: header('Location: /admin/something') or header("Location: /admin/something")
    // Also handle optional trailing ` exit;` on the same line
    $content = preg_replace_callback(
        '/header\s*\(\s*[\'"]Location:\s*\/admin\/([^\'"]+)[\'"]\s*\)\s*;\s*(?:exit\s*;)?/i',
        function ($m) {
            // Skip if already using BASE_URL or dynamic expression
            if (stripos($m[1], 'BASE_URL') !== false || stripos($m[1], 'defined') !== false) {
                return $m[0];
            }
            return "header('Location: ' . BASE_URL . '/admin/" . $m[1] . "'); exit;";
        },
        $content, -1, $cnt
    );
    $count += $cnt;
    
    if ($count > 0) {
        $fileStats['header_redirect_fixed'] += $count;
    }
    
    return [$content, $count];
}

function fixDoctypeHtmlHeadBody($content, &$fileStats) {
    $count = 0;
    
    // Remove <!DOCTYPE html> (with possible whitespace)
    $content = preg_replace('/^\s*<!DOCTYPE\s+html[^>]*>\s*$/mi', '', $content, -1, $cnt);
    $count += $cnt;
    if ($cnt > 0) {
        $fileStats['doctype_removed'] += $cnt;
    }
    
    // Remove <html ...> and </html> tags
    $content = preg_replace('/^\s*<html[^>]*>\s*$/mi', '', $content, -1, $cnt1);
    $content = preg_replace('/^\s*<\/html>\s*$/mi', '', $content, -1, $cnt2);
    $count += $cnt1 + $cnt2;
    
    // Remove <head> and </head> tags
    $content = preg_replace('/^\s*<head>\s*$/mi', '', $content, -1, $cnt1);
    $content = preg_replace('/^\s*<\/head>\s*$/mi', '', $content, -1, $cnt2);
    $count += $cnt1 + $cnt2;
    
    // Remove <body[^>]*> and </body> tags (only at line start)
    $content = preg_replace('/^\s*<body[^>]*>\s*$/mi', '', $content, -1, $cnt1);
    $content = preg_replace('/^\s*<\/body>\s*$/mi', '', $content, -1, $cnt2);
    $count += $cnt1 + $cnt2;
    
    // Remove <meta charset=...>, <meta name=viewport...>, <title>...</title> that are inside what was the head
    // These are redundant since the layout provides them
    $content = preg_replace('/^\s*<meta\s+charset=[^>]+>\s*$/mi', '', $content, -1, $cnt1);
    $content = preg_replace('/^\s*<meta\s+name=["\']viewport["\'][^>]*>\s*$/mi', '', $content, -1, $cnt2);
    $content = preg_replace('/^\s*<title>[^<]*<\/title>\s*$/mi', '', $content, -1, $cnt3);
    $count += $cnt1 + $cnt2 + $cnt3;
    
    if ($count > 0) {
        $fileStats['html_head_body_removed'] += $count;
    }
    
    return [$content, $count];
}

// Combined regex for CDN bootstrap/fontawesome links
function fixCdnLinks($content, &$fileStats) {
    $count = 0;
    
    // Match CDN links for bootstrap CSS
    $patterns = [
        '/<link\s+[^>]*href=["\']https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap[^"\']*["\'][^>]*\/?>/i',
        '/<link\s+[^>]*href=["\']https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/bootstrap[^"\']*["\'][^>]*\/?>/i',
        '/<link\s+[^>]*href=["\']https:\/\/maxcdn\.bootstrapcdn\.com[^"\']*["\'][^>]*\/?>/i',
        '/<link\s+[^>]*href=["\']https:\/\/stackpath\.bootstrapcdn\.com[^"\']*["\'][^>]*\/?>/i',
        // Font Awesome CDN links
        '/<link\s+[^>]*href=["\']https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/font-awesome[^"\']*["\'][^>]*\/?>/i',
        '/<link\s+[^>]*href=["\']https:\/\/use\.fontawesome\.com[^"\']*["\'][^>]*\/?>/i',
        '/<link\s+[^>]*href=["\']https:\/\/cdn\.jsdelivr\.net\/npm\/@fortawesome[^"\']*["\'][^>]*\/?>/i',
        '/<link\s+[^>]*href=["\']https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/font-awesome[^"\']*["\'][^>]*\/?>/i',
        // Bootstrap JS CDN
        '/<script\s+[^>]*src=["\']https:\/\/cdn\.jsdelivr\.net\/npm\/bootstrap[^"\']*["\'][^>]*><\/script>/i',
        '/<script\s+[^>]*src=["\']https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/bootstrap[^"\']*["\'][^>]*><\/script>/i',
        // Font Awesome JS CDN
        '/<script\s+[^>]*src=["\']https:\/\/kit\.fontawesome\.com[^"\']*["\'][^>]*><\/script>/i',
        '/<script\s+[^>]*src=["\']https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/font-awesome[^"\']*["\'][^>]*><\/script>/i',
    ];
    
    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '', $content, -1, $cnt);
        $count += $cnt;
    }
    
    if ($count > 0) {
        $fileStats['cdn_links_removed'] += $count;
    }
    
    return [$content, $count];
}

function cleanupBlankLines($content) {
    // Clean up multiple consecutive blank lines (2+ → 1)
    $content = preg_replace("/\n{3,}/", "\n\n", $content);
    return $content;
}

function cleanupDuplicateExit($content) {
    // Fix: header(...); exit;\nexit;  →  header(...); exit;
    $content = preg_replace('/exit;\s*\n\s*exit\s*;/', 'exit;', $content);
    return $content;
}

// --- Main ---

// Reset log
file_put_contents($logFile, "=== fix_admin_views.php Report ===" . PHP_EOL);
file_put_contents($logFile, "Started: " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND | LOCK_EX);
file_put_contents($logFile, "Scanning: $adminViewsDir" . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);

echo "=== APS Dream Home - Admin View Fixer ===" . PHP_EOL;
echo "Started: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($adminViewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$skipPaths = [
    'layouts/unified.php',
    'layouts/admin.php',
    'layouts/header.php',
    'layouts/footer.php',
    'login.php',
    'payments/receipt.php',
];

// Normalize separator for cross-platform
$adminViewsDirNorm = str_replace('\\', '/', $adminViewsDir);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    
    $path = $file->getRealPath();
    $pathNorm = str_replace('\\', '/', $path);
    
    // Skip layout files themselves (they intentionally contain DOCTYPE, CDN links, etc.)
    // Use suffix matching to work reliably across platforms
    $skipSuffixes = [
        '/layouts/unified.php',
        '/layouts/admin.php',
        '/layouts/header.php',
        '/layouts/footer.php',
        '/login.php',
        '/payments/receipt.php',
    ];
    $shouldSkip = false;
    foreach ($skipSuffixes as $suffix) {
        if (substr($pathNorm, -strlen($suffix)) === $suffix) {
            $shouldSkip = true;
            break;
        }
    }
    if ($shouldSkip) {
        continue;
    }
    $stats['scanned']++;
    
    // Read file
    $content = file_get_contents($path);
    if ($content === false) {
        $stats['errors']++;
        $stats['error_details'][] = "Cannot read: $path";
        logMsg("ERROR: Cannot read file", $path);
        continue;
    }
    
    $original = $content;
    $fileStats = [
        'session_start_removed' => 0,
        'header_redirect_fixed' => 0,
        'doctype_removed' => 0,
        'html_head_body_removed' => 0,
        'cdn_links_removed' => 0,
    ];
    
    // Apply fixes
    list($content, $cnt) = fixSessionStart($content, $fileStats);
    list($content, $cnt) = fixHeaderRedirects($content, $fileStats);
    list($content, $cnt) = fixDoctypeHtmlHeadBody($content, $fileStats);
    list($content, $cnt) = fixCdnLinks($content, $fileStats);
    
    $content = cleanupDuplicateExit($content);
    $content = cleanupBlankLines($content);
    
    // Check if anything changed
    if ($content !== $original) {
        // Backup
        backupFile($path);
        
        // Write back
        $written = file_put_contents($path, $content, LOCK_EX);
        if ($written === false) {
            $stats['errors']++;
            $stats['error_details'][] = "Cannot write: $path";
            logMsg("ERROR: Cannot write file", $path);
            continue;
        }
        
        $stats['modified']++;
        
        // Accumulate stats
        foreach ($fileStats as $key => $val) {
            $stats['fixes'][$key] += $val;
        }
        
        $fixList = [];
        foreach ($fileStats as $key => $val) {
            if ($val > 0) {
                $fixList[] = "$key: $val";
            }
        }
        logMsg("FIXED: " . implode(', ', $fixList), $path);
    }
}

// --- Report ---
echo PHP_EOL . "==========================================" . PHP_EOL;
echo "REPORT" . PHP_EOL;
echo "==========================================" . PHP_EOL;
echo "Files scanned:  " . $stats['scanned'] . PHP_EOL;
echo "Files modified: " . $stats['modified'] . PHP_EOL;
echo "Errors:         " . $stats['errors'] . PHP_EOL;
echo PHP_EOL;
echo "Fixes applied:" . PHP_EOL;
echo "  session_start() removed:     " . $stats['fixes']['session_start_removed'] . PHP_EOL;
echo "  header() redirects fixed:    " . $stats['fixes']['header_redirect_fixed'] . PHP_EOL;
echo "  DOCTYPE removed:             " . $stats['fixes']['doctype_removed'] . PHP_EOL;
echo "  html/head/body tags removed: " . $stats['fixes']['html_head_body_removed'] . PHP_EOL;
echo "  CDN links removed:           " . $stats['fixes']['cdn_links_removed'] . PHP_EOL;
echo PHP_EOL;

if ($stats['errors'] > 0) {
    echo "Errors:" . PHP_EOL;
    foreach ($stats['error_details'] as $err) {
        echo "  - $err" . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "Finished: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "Log file: $logFile" . PHP_EOL;

// Also write to log
file_put_contents($logFile, PHP_EOL . "=== Final Report ===" . PHP_EOL, FILE_APPEND | LOCK_EX);
file_put_contents($logFile, "Scanned: {$stats['scanned']}, Modified: {$stats['modified']}, Errors: {$stats['errors']}" . PHP_EOL, FILE_APPEND | LOCK_EX);
foreach ($stats['fixes'] as $key => $val) {
    file_put_contents($logFile, "  $key: $val" . PHP_EOL, FILE_APPEND | LOCK_EX);
}
