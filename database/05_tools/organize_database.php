<?php
/**
 * APS Dream Homes - Database Organization Script
 * This script organizes and cleans up database files
 */

echo "=== APS Dream Homes - Database Organization ===\n\n";

// Define directories
$baseDir = __DIR__;
$databaseDir = $baseDir . '/database';

$directories = [
    'backups' => $databaseDir . '/backups',
    'migrations' => $databaseDir . '/migrations',
    'seeders' => $databaseDir . '/seeders',
    'current' => $databaseDir . '/current',
    'archive' => $databaseDir . '/archive'
];

// Create directories if they don't exist
foreach ($directories as $name => $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "Created directory: $name ($path)\n";
    }
}

// File organization patterns
$filePatterns = [
    'backups' => [
        'patterns' => ['*backup*', '*bak*', '*old*', '*previous*'],
        'extensions' => ['sql']
    ],
    'migrations' => [
        'patterns' => ['*migration*', '*patch*', '*update*', '*upgrade*'],
        'extensions' => ['sql', 'php']
    ],
    'seeders' => [
        'patterns' => ['*sample*', '*seed*', '*demo*', '*test*', '*fake*'],
        'extensions' => ['sql', 'php']
    ],
    'current' => [
        'patterns' => ['*current*', '*main*', '*complete*', '*full*'],
        'extensions' => ['sql']
    ]
];

// Get all SQL files in database directory
$sqlFiles = glob($databaseDir . '/*.sql');
$phpFiles = glob($databaseDir . '/*.php');

echo "\nFound " . count($sqlFiles) . " SQL files and " . count($phpFiles) . " PHP files to organize.\n\n";

// Organize files
$movedCount = 0;
foreach ($filePatterns as $category => $patterns) {
    echo "Organizing $category files...\n";

    foreach ($patterns['patterns'] as $pattern) {
        foreach (glob($databaseDir . '/' . $pattern) as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $newPath = $directories[$category] . '/' . $filename;

                if (!file_exists($newPath)) {
                    if (rename($file, $newPath)) {
                        echo "  ✓ Moved: $filename -> $category/\n";
                        $movedCount++;
                    } else {
                        echo "  ✗ Failed to move: $filename\n";
                    }
                } else {
                    echo "  - Already exists: $filename in $category/\n";
                }
            }
        }
    }
}

// Move remaining large SQL files to current
echo "\nMoving large SQL files to current directory...\n";
foreach ($sqlFiles as $file) {
    if (is_file($file)) {
        $size = filesize($file);
        if ($size > 1000000) { // Files larger than 1MB
            $filename = basename($file);
            $newPath = $directories['current'] . '/' . $filename;

            if (!file_exists($newPath)) {
                if (rename($file, $newPath)) {
                    echo "  ✓ Moved large file: $filename ($size bytes) -> current/\n";
                    $movedCount++;
                }
            }
        }
    }
}

// Create organization report
echo "\n=== Organization Summary ===\n";
echo "Total files moved: $movedCount\n";

foreach ($directories as $name => $path) {
    $fileCount = count(glob($path . '/*'));
    echo "$name/: $fileCount files\n";
}

// Create README for organized structure
$readmeContent = "# APS Dream Homes - Database Organization

## Directory Structure

```
database/
├── current/           # Main database files (apsdreamhomes.sql, etc.)
├── backups/           # Database backup files
├── migrations/        # Database migration and update scripts
├── seeders/           # Sample data and seed files
└── archive/           # Archived/old database files
```

## Main Database Files

- `current/aps_dream_homes_current.sql` - Main database schema and data
- `current/aps_dream_homes_main_config.sql` - Header/footer settings and configuration
- `backups/` - All backup files organized by date
- `migrations/` - Database patches and updates

## Setup Instructions

1. Import the main database: `current/aps_dream_homes_current.sql`
2. Run configuration: `current/aps_dream_homes_main_config.sql`
3. Check migrations folder for any additional updates needed

## Last Organized

" . date('Y-m-d H:i:s') . "
";

file_put_contents($databaseDir . '/DATABASE_ORGANIZATION_README.md', $readmeContent);

echo "\n✓ Database organization completed!\n";
echo "✓ Created organization report: database/DATABASE_ORGANIZATION_README.md\n";
echo "\nYour database files are now properly organized and ready to use!\n";

?>
