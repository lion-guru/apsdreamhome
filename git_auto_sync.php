<?php
/**
 * APS Dream Home - Git Auto Sync Script
 * Bidirectional synchronization between local and remote systems
 */

// Configuration
$remoteUrl = 'https://github.com/lion-guru/apsdreamhome.git';
$syncInterval = 30; // seconds between sync checks
$logFile = __DIR__ . '/git_sync.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

// Function to run Git command
function runGitCommand($command, $cwd = null) {
    $cwd = $cwd ?: __DIR__;
    $fullCommand = "cd \"$cwd\" && git $command 2>&1";
    $output = shell_exec($fullCommand);
    return $output;
}

// Function to check for local changes
function hasLocalChanges() {
    $status = runGitCommand('status --porcelain');
    return !empty(trim($status));
}

// Function to pull remote changes
function pullRemoteChanges() {
    logMessage("Pulling remote changes...");
    $output = runGitCommand('pull --rebase origin main');

    if (strpos($output, 'error') !== false || strpos($output, 'fatal') !== false) {
        logMessage("Pull failed: $output");
        return false;
    }

    logMessage("Pull successful");
    return true;
}

// Function to push local changes
function pushLocalChanges() {
    if (!hasLocalChanges()) {
        return true; // No changes to push
    }

    logMessage("Pushing local changes...");
    $output = runGitCommand('add -A');

    if (strpos($output, 'error') !== false || strpos($output, 'fatal') !== false) {
        logMessage("Add failed: $output");
        return false;
    }

    $output = runGitCommand('commit -m "Auto-sync: local changes"');

    // If no changes to commit, that's OK
    if (strpos($output, 'nothing to commit') !== false) {
        return true;
    }

    if (strpos($output, 'error') !== false || strpos($output, 'fatal') !== false) {
        logMessage("Commit failed: $output");
        return false;
    }

    $output = runGitCommand('push origin main');

    if (strpos($output, 'error') !== false || strpos($output, 'fatal') !== false) {
        logMessage("Push failed: $output");
        return false;
    }

    logMessage("Push successful");
    return true;
}

// Function to perform bidirectional sync
function performSync() {
    logMessage("=== Starting Git Auto-Sync ===");

    // First pull remote changes
    if (!pullRemoteChanges()) {
        logMessage("Pull failed, skipping sync");
        return false;
    }

    // Then push any local changes
    if (!pushLocalChanges()) {
        logMessage("Push failed, but pull was successful");
        return false;
    }

    logMessage("=== Git Auto-Sync Complete ===");
    return true;
}

// Main execution
logMessage("Git Auto-Sync Script Started");
logMessage("Remote: $remoteUrl");
logMessage("Sync Interval: {$syncInterval} seconds");
logMessage("Log File: $logFile");

// Perform initial sync
performSync();

// Continuous sync loop (commented out for manual execution)
/*
while (true) {
    performSync();
    sleep($syncInterval);
}
*/

logMessage("Git Auto-Sync Script Finished");
?>
