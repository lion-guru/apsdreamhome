<?php
$targetDir = __DIR__;
$excludeDirs = ['vendor', 'node_modules', '.git'];

$missingClasses = [
    'AIController', 'PlottingController', 'MediaLibraryController', 'MediaController', 
    'EventController', 'PerformanceController', 'MarketingAutomationController', 
    'SmsController', 'AlertController', 'AsyncController', 'SecurityController', 
    'CareerController', 'MarketingController', 'FarmerController', 'LandController', 
    'CustomFeaturesController', 'ProjectController', 'CustomerController', 
    'MCPController', 'FileController', 'HelperController', 'LocalizationController', 
    'BackupIntegrityController', 'SalaryController', 'ReportController', 
    'AssociateController', 'UserController', 'CampaignController'
];

$foundClasses = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isDir()) {
        foreach ($excludeDirs as $exclude) {
            if (strpos($file->getPathname(), DIRECTORY_SEPARATOR . $exclude) !== false) {
                continue 2; // Skip excluded directories
            }
        }
    }

    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        foreach ($missingClasses as $class) {
            // Regex to find "class ClassName"
            if (preg_match("/class\s+{$class}\b/i", $content)) {
                $foundClasses[$class][] = str_replace($targetDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
    }
}

echo "=== Deep Scan Results ===\n";
if (empty($foundClasses)) {
    echo "None of the missing classes were found anywhere in the project.\n";
} else {
    foreach ($foundClasses as $className => $paths) {
        echo "Found {$className} in:\n";
        foreach ($paths as $path) {
            echo "  - {$path}\n";
        }
    }
}
?>
