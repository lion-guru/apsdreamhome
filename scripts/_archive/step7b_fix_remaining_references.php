<?php
/**
 * Step 7b: Fix remaining code references
 * More aggressive replacement to catch all references
 */

$projectRoot = dirname(__DIR__);
echo "=== STEP 7b: FIX REMAINING CODE REFERENCES ===\n\n";

// Define comprehensive replacements
$replacements = [
    'activity_logs' => 'activity_logs_unified',
    'admin_activity_log' => 'activity_logs_unified', 
    'admin_audit_logs' => 'activity_logs_unified',
    'login_attempts' => 'activity_logs_unified',
    'login_logs' => 'activity_logs_unified',
    'failed_login_attempts' => 'activity_logs_unified',
    'email_logs' => 'notifications_unified',
    'email_tracking' => 'notifications_unified',
    'sms_logs' => 'notifications_unified',
    'sms_otp_logs' => 'notifications_unified',
    'notification_history' => 'notifications_unified',
    'agent_details' => 'users'
];

echo "Aggressively replacing remaining references...\n\n";

$filesUpdated = 0;
$filesSkipped = 0;

$searchDirs = ['app', 'routes', 'public'];

foreach ($searchDirs as $dir) {
    $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($fullDir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if (strpos($file->getPathname(), 'vendor') !== false) continue;
        if (strpos($file->getPathname(), 'node_modules') !== false) continue;
        if (strpos($file->getPathname(), '.git') !== false) continue;
        
        // Skip backup/deleted files
        if (strpos($file->getPathname(), '.deleted') !== false) continue;
        if (strpos($file->getPathname(), '.backup') !== false) continue;
        
        // Only process PHP files
        $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
        if ($ext !== 'php') continue;
        
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        if ($content === false) continue;
        
        // Apply all replacements
        foreach ($replacements as $old => $new) {
            // More aggressive pattern matching
            $patterns = [
                "/\b$old\b/i", // word boundaries
                "/'$old'/i", // single quoted
                "/\"$old\"/i", // double quoted
                "/`$old`/i", // backtick quoted
            ];
            
            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, $new, $content);
            }
        }
        
        // Only write if content changed
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $filesUpdated++;
            $relativePath = str_replace($projectRoot, '', $filePath);
            echo "✓ Updated: $relativePath\n";
        }
    }
}

echo "\n";

// Check for remaining references after aggressive replacement
echo "Final check for remaining references...\n\n";

$remainingReferences = 0;
foreach ($replacements as $old => $new) {
    $count = 0;
    
    foreach ($searchDirs as $dir) {
        $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullDir)) continue;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (strpos($file->getPathname(), 'vendor') !== false) continue;
            if (strpos($file->getPathname(), 'node_modules') !== false) continue;
            if (strpos($file->getPathname(), '.git') !== false) continue;
            if (strpos($file->getPathname(), '.deleted') !== false) continue;
            if (strpos($file->getPathname(), '.backup') !== false) continue;
            
            $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if ($ext !== 'php') continue;
            
            $content = file_get_contents($file->getPathname());
            if ($content && preg_match("/\b$old\b/i", $content)) {
                $count++;
                if ($count <= 5) {
                    $relativePath = str_replace($projectRoot, '', $file->getPathname());
                    echo "  $old found in: $relativePath\n";
                }
            }
        }
    }
    
    if ($count > 0) {
        echo "\n⚠️  $old: $count remaining references\n";
        $remainingReferences += $count;
    } else {
        echo "✓ $old: No remaining references\n";
    }
}

echo "\n=== STEP 7b COMPLETE ===\n";
echo "Summary:\n";
echo "  - Files updated in this pass: $filesUpdated\n";
echo "  - Total remaining references: $remainingReferences\n";
echo "  - Code migration status: " . ($remainingReferences === 0 ? "COMPLETE" : "PARTIAL") . "\n";

if ($remainingReferences > 0) {
    echo "\n⚠️  Note: Some references may be in comments, strings, or context-dependent.\n";
    echo "   Manual review may be needed for remaining references.\n";
} else {
    echo "\n✓ All code references successfully updated!\n";
}
