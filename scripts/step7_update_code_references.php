<?php
/**
 * Step 7: Update code references for all merged tables
 * CRITICAL: This prevents table recreation by the application
 */

$projectRoot = dirname(__DIR__);
echo "=== STEP 7: UPDATE CODE REFERENCES FOR MERGED TABLES ===\n\n";

// Define the consolidations we've done
$consolidations = [
    'agent_details' => [
        'new_reference' => 'users',
        'column_mappings' => [
            'license_number' => 'agent_license_number',
            'experience_years' => 'agent_experience_years', 
            'specialization' => 'agent_specialization'
        ],
        'search_patterns' => ['/agent_details/i', '/AgentDetail/i']
    ],
    'activity_logs' => [
        'new_reference' => 'activity_logs_unified',
        'column_mappings' => [],
        'search_patterns' => ['/activity_logs/i', '/admin_activity_log/i', '/admin_audit_logs/i', '/login_attempts/i', '/login_logs/i', '/failed_login_attempts/i']
    ],
    'email_logs' => [
        'new_reference' => 'notifications_unified',
        'column_mappings' => [
            'notification_type' => 'email'
        ],
        'search_patterns' => ['/email_logs/i', '/email_tracking/i']
    ],
    'sms_logs' => [
        'new_reference' => 'notifications_unified',
        'column_mappings' => [
            'notification_type' => 'sms'
        ],
        'search_patterns' => ['/sms_logs/i', '/sms_otp_logs/i']
    ],
    'notification_history' => [
        'new_reference' => 'notifications_unified',
        'column_mappings' => [],
        'search_patterns' => ['/notification_history/i']
    ]
];

echo "Searching and updating code references...\n\n";

$filesUpdated = 0;
$filesSkipped = 0;
$totalReplacements = 0;

foreach ($consolidations as $oldTable => $config) {
    echo "📋 Processing: $oldTable → {$config['new_reference']}\n";
    
    // Search directories
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
            
            // Skip specific file types
            $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if (!in_array($ext, ['php', 'html', 'js'])) continue;
            
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);
            $originalContent = $content;
            
            if ($content === false) continue;
            
            // Apply search patterns and replacements
            foreach ($config['search_patterns'] as $pattern) {
                if (preg_match($pattern, $content)) {
                    // Replace table name in code
                    $content = preg_replace($pattern, $config['new_reference'], $content);
                    
                    // Replace column names if specified
                    foreach ($config['column_mappings'] as $oldCol => $newCol) {
                        $content = preg_replace("/\b$oldCol\b/i", $newCol, $content);
                    }
                }
            }
            
            // Only write if content changed
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content);
                $filesUpdated++;
                $relativePath = str_replace($projectRoot, '', $filePath);
                echo "  ✓ Updated: $relativePath\n";
                $totalReplacements++;
            }
        }
    }
    
    echo "\n";
}

// Additional specific file updates for known problematic files
echo "📋 Specific file updates:\n";

$specificUpdates = [
    'app/Core/EmailManager.php' => [
        'email_logs' => 'notifications_unified'
    ],
    'app/Core/NotificationService.php' => [
        'notification_history' => 'notifications_unified'
    ],
    'app/Middleware/AuthMiddleware.php' => [
        'login_attempts' => 'activity_logs_unified',
        'login_logs' => 'activity_logs_unified'
    ],
    'app/Core/Auth/AuthService.php' => [
        'admin_activity_log' => 'activity_logs_unified',
        'admin_audit_logs' => 'activity_logs_unified'
    ]
];

foreach ($specificUpdates as $filePath => $replacements) {
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $filePath;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $originalContent = $content;
        
        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($fullPath, $content);
            echo "  ✓ Updated: $filePath\n";
            $filesUpdated++;
        }
    }
}

echo "\n";

// Final verification - check for remaining references
echo "🔍 Checking for remaining references to old tables...\n\n";

$remainingReferences = [];
foreach ($consolidations as $oldTable => $config) {
    foreach ($config['search_patterns'] as $pattern) {
        foreach ($searchDirs as $dir) {
            $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($fullDir)) continue;

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
            foreach ($iterator as $file) {
                if ($file->isDir()) continue;
                if (strpos($file->getPathname(), 'vendor') !== false) continue;
                if (strpos($file->getPathname(), 'node_modules') !== false) continue;
                
                $content = file_get_contents($file->getPathname());
                if ($content && preg_match($pattern, $content)) {
                    $relativePath = str_replace($projectRoot, '', $file->getPathname());
                    $remainingReferences[] = "$oldTable found in $relativePath";
                }
            }
        }
    }
}

if (!empty($remainingReferences)) {
    echo "⚠️  Remaining references found:\n";
    foreach (array_unique($remainingReferences) as $ref) {
        echo "  - $ref\n";
    }
} else {
    echo "✓ No remaining references to old tables found\n";
}

echo "\n=== STEP 7 COMPLETE ===\n";
echo "Summary:\n";
echo "  - Files updated: $filesUpdated\n";
echo "  - Total replacements made: $totalReplacements\n";
echo "  - Remaining references: " . count(array_unique($remainingReferences)) . "\n";
echo "  - Code now uses unified tables instead of fragmented tables\n";
echo "\nNEXT: Apply proper naming conventions and test changes\n";
