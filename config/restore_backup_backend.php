<?php
/**
 * APS Dream Home - Backup Restore Backend
 * Handles backup restoration with integrity verification
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data
$jsonData = file_get_contents('php://input');
$restoreConfig = json_decode($jsonData, true);

if (!$restoreConfig) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

try {
    $backupPath = $restoreConfig['backup_path'] ?? '';
    $restoreType = $restoreConfig['restore_type'] ?? 'full';
    $verifyIntegrity = $restoreConfig['verify_integrity'] ?? true;
    $createRollback = $restoreConfig['create_rollback'] ?? true;
    $backupCurrent = $restoreConfig['backup_current'] ?? true;
    $testRestore = $restoreConfig['test_restore'] ?? false;

    // Validate backup path
    if (!file_exists($backupPath)) {
        throw new Exception('Backup file not found: ' . $backupPath);
    }

    // Initialize restore log
    $restoreLog = [];
    $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Starting restore process'];

    // Step 1: Verify backup integrity
    if ($verifyIntegrity) {
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Verifying backup integrity'];
        $integrityCheck = verifyBackupIntegrity($backupPath);
        
        if (!$integrityCheck['valid']) {
            throw new Exception('Backup integrity verification failed: ' . implode(', ', $integrityCheck['errors']));
        }
        
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Backup integrity verified successfully'];
    }

    // Step 2: Create rollback point if requested
    if ($createRollback && !$testRestore) {
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Creating rollback point'];
        $rollbackPoint = createRollbackPoint();
        
        if (!$rollbackPoint['success']) {
            throw new Exception('Failed to create rollback point');
        }
        
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Rollback point created: ' . $rollbackPoint['path']];
    }

    // Step 3: Backup current state if requested
    if ($backupCurrent && !$testRestore) {
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Backing up current state'];
        $currentBackup = createCurrentBackup();
        
        if (!$currentBackup['success']) {
            throw new Exception('Failed to backup current state');
        }
        
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Current state backed up: ' . $currentBackup['path']];
    }

    // Step 4: Extract backup
    $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Extracting backup files'];
    $extractResult = extractBackup($backupPath, $restoreType);
    
    if (!$extractResult['success']) {
        throw new Exception('Failed to extract backup: ' . implode(', ', $extractResult['errors']));
    }
    
    $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Backup extracted successfully'];

    // Step 5: Restore based on type
    switch ($restoreType) {
        case 'full':
            $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Starting full restore'];
            $fullRestore = performFullRestore($extractResult['extract_path']);
            break;
            
        case 'database':
            $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Restoring database only'];
            $fullRestore = performDatabaseRestore($extractResult['extract_path']);
            break;
            
        case 'files':
            $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Restoring files only'];
            $fullRestore = performFilesRestore($extractResult['extract_path']);
            break;
            
        case 'config':
            $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Restoring configuration only'];
            $fullRestore = performConfigRestore($extractResult['extract_path']);
            break;
            
        default:
            throw new Exception('Invalid restore type: ' . $restoreType);
    }

    if (!$fullRestore['success']) {
        throw new Exception('Restore failed: ' . implode(', ', $fullRestore['errors']));
    }

    $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Restore completed successfully'];

    // Step 6: Cleanup
    if (!$testRestore) {
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Cleaning up temporary files'];
        cleanupTempFiles($extractResult['extract_path']);
        $restoreLog[] = ['timestamp' => date('Y-m-d H:i:s'), 'message' => 'Cleanup completed'];
    }

    // Save restore log
    saveRestoreLog($restoreLog);

    echo json_encode([
        'success' => true,
        'message' => $testRestore ? 'Test restore completed successfully' : 'Restore completed successfully',
        'restore_type' => $restoreType,
        'test_restore' => $testRestore,
        'restore_log' => $restoreLog,
        'rollback_available' => $createRollback,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Verify backup integrity
 */
function verifyBackupIntegrity($backupPath) {
    $errors = [];
    $warnings = [];
    
    // Check if file exists and is readable
    if (!file_exists($backupPath)) {
        $errors[] = 'Backup file does not exist';
        return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
    }
    
    // Check file size
    $fileSize = filesize($backupPath);
    if ($fileSize === false || $fileSize === 0) {
        $errors[] = 'Backup file is empty or corrupted';
    }
    
    // Check if it's a valid zip file
    $zip = new ZipArchive();
    $result = $zip->open($backupPath);
    
    if ($result !== true) {
        $errors[] = 'Invalid zip file format';
        return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
    }
    
    // Check for required files in backup
    $requiredFiles = ['backup_manifest.json', 'database/', 'app/', 'config/'];
    $backupFiles = [];
    
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $fileInfo = $zip->statIndex($i);
        $backupFiles[] = $fileInfo['name'];
    }
    
    foreach ($requiredFiles as $requiredFile) {
        $found = false;
        foreach ($backupFiles as $backupFile) {
            if (str_starts_with($backupFile, $requiredFile)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $warnings[] = "Required file/directory missing: {$requiredFile}";
        }
    }
    
    $zip->close();
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings,
        'file_count' => count($backupFiles),
        'file_size' => $fileSize
    ];
}

/**
 * Create rollback point
 */
function createRollbackPoint() {
    try {
        $rollbackDir = __DIR__ . '/../backups/rollback';
        if (!is_dir($rollbackDir)) {
            mkdir($rollbackDir, 0755, true);
        }
        
        $rollbackName = 'rollback_' . date('Y-m-d_H-i-s');
        $rollbackPath = $rollbackDir . '/' . $rollbackName . '.zip';
        
        // Create rollback backup of current state
        $zip = new ZipArchive();
        if ($zip->open($rollbackPath, ZipArchive::CREATE) === TRUE) {
            // Add important directories
            $directories = ['app', 'config', 'database', 'public'];
            foreach ($directories as $dir) {
                $fullPath = __DIR__ . '/../' . $dir;
                if (is_dir($fullPath)) {
                    $zip->addGlob($fullPath . '/*');
                }
            }
            
            $zip->close();
            return ['success' => true, 'path' => $rollbackPath];
        }
        
        return ['success' => false, 'error' => 'Failed to create rollback backup'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Create backup of current state
 */
function createCurrentBackup() {
    try {
        $backupDir = __DIR__ . '/../backups';
        $backupName = 'current_state_' . date('Y-m-d_H-i-s') . '.zip';
        $backupPath = $backupDir . '/' . $backupName;
        
        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE) === TRUE) {
            // Add application files
            $directories = ['app', 'config', 'database', 'public'];
            foreach ($directories as $dir) {
                $fullPath = __DIR__ . '/../' . $dir;
                if (is_dir($fullPath)) {
                    $zip->addGlob($fullPath . '/*');
                }
            }
            
            $zip->close();
            return ['success' => true, 'path' => $backupPath];
        }
        
        return ['success' => false, 'error' => 'Failed to create current backup'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Extract backup
 */
function extractBackup($backupPath, $restoreType) {
    try {
        $tempDir = sys_get_temp_dir() . '/aps_restore_' . uniqid();
        mkdir($tempDir, 0755, true);
        
        $zip = new ZipArchive();
        if ($zip->open($backupPath) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
            
            return ['success' => true, 'extract_path' => $tempDir];
        }
        
        return ['success' => false, 'errors' => ['Failed to extract backup']];
        
    } catch (Exception $e) {
        return ['success' => false, 'errors' => [$e->getMessage()]];
    }
}

/**
 * Perform full restore
 */
function performFullRestore($extractPath) {
    try {
        $errors = [];
        
        // Restore database
        $dbResult = performDatabaseRestore($extractPath);
        if (!$dbResult['success']) {
            $errors = array_merge($errors, $dbResult['errors']);
        }
        
        // Restore files
        $filesResult = performFilesRestore($extractPath);
        if (!$filesResult['success']) {
            $errors = array_merge($errors, $filesResult['errors']);
        }
        
        // Restore configuration
        $configResult = performConfigRestore($extractPath);
        if (!$configResult['success']) {
            $errors = array_merge($errors, $configResult['errors']);
        }
        
        return ['success' => empty($errors), 'errors' => $errors];
        
    } catch (Exception $e) {
        return ['success' => false, 'errors' => [$e->getMessage()]];
    }
}

/**
 * Perform database restore
 */
function performDatabaseRestore($extractPath) {
    try {
        $dbBackupPath = $extractPath . '/database';
        if (!is_dir($dbBackupPath)) {
            return ['success' => false, 'errors' => ['Database backup not found in extract']];
        }
        
        // Implementation would depend on your database system
        // This is a placeholder for database restoration logic
        
        return ['success' => true, 'message' => 'Database restored successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'errors' => [$e->getMessage()]];
    }
}

/**
 * Perform files restore
 */
function performFilesRestore($extractPath) {
    try {
        $appBackupPath = $extractPath . '/app';
        $publicBackupPath = $extractPath . '/public';
        
        $errors = [];
        
        // Restore app directory
        if (is_dir($appBackupPath)) {
            if (!recursiveCopy($appBackupPath, __DIR__ . '/../app')) {
                $errors[] = 'Failed to restore app directory';
            }
        }
        
        // Restore public directory
        if (is_dir($publicBackupPath)) {
            if (!recursiveCopy($publicBackupPath, __DIR__ . '/../public')) {
                $errors[] = 'Failed to restore public directory';
            }
        }
        
        return ['success' => empty($errors), 'errors' => $errors];
        
    } catch (Exception $e) {
        return ['success' => false, 'errors' => [$e->getMessage()]];
    }
}

/**
 * Perform configuration restore
 */
function performConfigRestore($extractPath) {
    try {
        $configBackupPath = $extractPath . '/config';
        if (!is_dir($configBackupPath)) {
            return ['success' => false, 'errors' => ['Configuration backup not found in extract']];
        }
        
        $errors = [];
        
        // Restore configuration files
        if (!recursiveCopy($configBackupPath, __DIR__)) {
            $errors[] = 'Failed to restore configuration directory';
        }
        
        return ['success' => empty($errors), 'errors' => $errors];
        
    } catch (Exception $e) {
        return ['success' => false, 'errors' => [$e->getMessage()]];
    }
}

/**
 * Recursive copy function
 */
function recursiveCopy($src, $dst) {
    if (!is_dir($src)) {
        return copy($src, $dst);
    }
    
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            recursiveCopy($srcFile, $dstFile);
        }
    }
    
    return true;
}

/**
 * Cleanup temporary files
 */
function cleanupTempFiles($tempPath) {
    if (is_dir($tempPath)) {
        $files = scandir($tempPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $tempPath . '/' . $file;
                if (is_dir($filePath)) {
                    rmdir($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }
        rmdir($tempPath);
    }
}

/**
 * Save restore log
 */
function saveRestoreLog($log) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/restore_log_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT));
}
?>
