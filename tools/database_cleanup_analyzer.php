<?php
/**
 * Database Folder Cleanup Tool for Abhay Singh
 * Organize and clean unnecessary database files
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$databaseDir = __DIR__ . '/database/';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Folder Cleanup - APS Dream Home</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .file-item { font-family: monospace; font-size: 0.9em; }
        .essential { background-color: #d4edda; }
        .archive { background-color: #fff3cd; }
        .delete { background-color: #f8d7da; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='text-center mb-4'>
        <h1><i class='fas fa-broom'></i> Database Folder Cleanup</h1>
        <p class='lead'>Organize your 196+ database files</p>
    </div>";

// Get all files in database directory
function getAllFiles($dir) {
    $files = [];
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
    }
    return $files;
}

$allFiles = getAllFiles($databaseDir);
$totalFiles = count($allFiles);

echo "<div class='alert alert-info text-center'>
    <h4>Found $totalFiles files in database folder</h4>
</div>";

// Categorize files
$essentialFiles = [
    'aps_complete_schema_part1.sql',
    'aps_complete_schema_part2.sql', 
    'aps_complete_schema_part3.sql',
    'seed_demo_data_final.sql',
    'seed_visit_management.sql',
    'complete_setup.sql',
    'system_health_check.php',
    'dashboard_data_manager.php',
    'README.md'
];

$archiveFiles = [];
$deleteFiles = [];
$keepFiles = [];

foreach ($allFiles as $file) {
    $fileName = basename($file);
    
    // Essential files to keep
    if (in_array($fileName, $essentialFiles)) {
        $keepFiles[] = $file;
    }
    // Archive worthy files
    elseif (
        strpos($fileName, 'backup') !== false ||
        strpos($fileName, 'schema') !== false ||
        strpos($fileName, 'migration') !== false ||
        preg_match('/\d{4}_\d{2}_\d{2}/', $fileName) ||
        strpos($fileName, 'test_') === 0
    ) {
        $archiveFiles[] = $file;
    }
    // Files safe to delete
    elseif (
        strpos($fileName, 'temp') !== false ||
        strpos($fileName, 'tmp') !== false ||
        strpos($fileName, 'duplicate') !== false ||
        strpos($fileName, 'old') !== false ||
        strpos($fileName, 'check_') === 0
    ) {
        $deleteFiles[] = $file;
    }
    // Everything else to keep
    else {
        $keepFiles[] = $file;
    }
}

echo "<div class='row'>
    <div class='col-md-4'>
        <div class='card'>
            <div class='card-header bg-success text-white'>
                <h5><i class='fas fa-star'></i> Essential Files (" . count($keepFiles) . ")</h5>
                <small>Must keep - production files</small>
            </div>
            <div class='card-body essential' style='max-height: 400px; overflow-y: auto;'>";

foreach ($keepFiles as $file) {
    $fileName = basename($file);
    $fileSize = file_exists($file) ? round(filesize($file) / 1024, 2) : 0;
    echo "<div class='file-item mb-1'>✅ $fileName <small class='text-muted'>({$fileSize}KB)</small></div>";
}

echo "</div>
        </div>
    </div>
    <div class='col-md-4'>
        <div class='card'>
            <div class='card-header bg-warning text-white'>
                <h5><i class='fas fa-archive'></i> Archive Files (" . count($archiveFiles) . ")</h5>
                <small>Move to archive folder</small>
            </div>
            <div class='card-body archive' style='max-height: 400px; overflow-y: auto;'>";

foreach ($archiveFiles as $file) {
    $fileName = basename($file);
    $fileSize = file_exists($file) ? round(filesize($file) / 1024, 2) : 0;
    echo "<div class='file-item mb-1'>📦 $fileName <small class='text-muted'>({$fileSize}KB)</small></div>";
}

echo "</div>
        </div>
    </div>
    <div class='col-md-4'>
        <div class='card'>
            <div class='card-header bg-danger text-white'>
                <h5><i class='fas fa-trash'></i> Delete Files (" . count($deleteFiles) . ")</h5>
                <small>Safe to remove</small>
            </div>
            <div class='card-body delete' style='max-height: 400px; overflow-y: auto;'>";

foreach ($deleteFiles as $file) {
    $fileName = basename($file);
    $fileSize = file_exists($file) ? round(filesize($file) / 1024, 2) : 0;
    echo "<div class='file-item mb-1'>🗑️ $fileName <small class='text-muted'>({$fileSize}KB)</small></div>";
}

echo "</div>
        </div>
    </div>
</div>";

// Summary
$totalSizeReduction = 0;
foreach (array_merge($archiveFiles, $deleteFiles) as $file) {
    if (file_exists($file)) {
        $totalSizeReduction += filesize($file);
    }
}
$totalSizeReduction = round($totalSizeReduction / (1024 * 1024), 2);

echo "<div class='mt-4'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h5><i class='fas fa-chart-pie'></i> Cleanup Summary</h5>
        </div>
        <div class='card-body'>
            <div class='row text-center'>
                <div class='col-md-3'>
                    <h3 class='text-success'>" . count($keepFiles) . "</h3>
                    <p>Files to Keep</p>
                </div>
                <div class='col-md-3'>
                    <h3 class='text-warning'>" . count($archiveFiles) . "</h3>
                    <p>Files to Archive</p>
                </div>
                <div class='col-md-3'>
                    <h3 class='text-danger'>" . count($deleteFiles) . "</h3>
                    <p>Files to Delete</p>
                </div>
                <div class='col-md-3'>
                    <h3 class='text-info'>{$totalSizeReduction}MB</h3>
                    <p>Space to Save</p>
                </div>
            </div>
        </div>
    </div>
</div>";

echo "<div class='mt-4 text-center'>
    <div class='alert alert-warning'>
        <h5><i class='fas fa-exclamation-triangle'></i> Ready to Clean Up?</h5>
        <p>This analysis shows what files can be safely organized. Your system will continue working normally.</p>
        <button class='btn btn-warning btn-lg me-2' onclick='performCleanup()'><i class='fas fa-broom'></i> Clean Up Now</button>
        <button class='btn btn-secondary btn-lg' onclick='window.location.reload()'><i class='fas fa-refresh'></i> Refresh Analysis</button>
    </div>
</div>";

echo "</div>

<script>
function performCleanup() {
    if (confirm('Are you sure you want to proceed with cleanup? This will organize your files but keep your system working.')) {
        window.location.href = 'database_cleanup_execute.php';
    }
}
</script>

</body>
</html>";
?>