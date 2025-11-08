<?php
require_once 'duplicate_file_cleanup.php';

try {
    $duplicateCleanup = new DuplicateFileCleanup();
    
    // Find duplicates
    $duplicates = $duplicateCleanup->findDuplicates();
    
    // Remove duplicates if found
    if (!empty($duplicates)) {
        $removedFiles = $duplicateCleanup->removeDuplicates();
        
        // Generate and save report
        $report = $duplicateCleanup->generateReport();
        $htmlReport = $duplicateCleanup->generateHTMLReport($report);
        file_put_contents(__DIR__ . '/logs/duplicate_file_cleanup_report.html', $htmlReport);
        
        echo "Duplicate File Cleanup Completed.\n";
        echo "Total Duplicates Found: " . count($duplicates) . "\n";
        echo "Files Removed: " . count($removedFiles) . "\n";
        echo "Report saved to: " . __DIR__ . "/logs/duplicate_file_cleanup_report.html\n";
    } else {
        echo "No duplicate files found.\n";
    }
} catch (Exception $e) {
    echo "Duplicate file cleanup failed: " . $e->getMessage() . "\n";
}
