<?php
/**
 * APS Dream Home - Phase 5 Deep Scan: Database Schema Verification
 * Comprehensive analysis of database schema, relationships, and migration integrity
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'database_scan',
    'summary' => [],
    'issues' => [],
    'schema_analysis' => [],
    'relationship_analysis' => [],
    'recommendations' => []
];

echo "🗄️  Phase 5: Database Schema Deep Analysis\n";
echo "=========================================\n\n";

// Check database configuration
echo "🔧 Checking Database Configuration\n";
echo "==================================\n";

$configPath = $projectRoot . '/config/database.php';
if (file_exists($configPath)) {
    echo "✅ Database config file found\n";

    // Define required constants for database config
    if (!defined('APP_ROOT')) define('APP_ROOT', $projectRoot);
    if (!defined('APP_ENV')) define('APP_ENV', 'production');

    // Define helper functions that might be needed
    if (!function_exists('env')) {
        function env($key, $default = null) {
            return $_ENV[$key] ?? $default;
        }
    }

    if (!function_exists('resource_path')) {
        function resource_path($path = '') {
            return APP_ROOT . '/resources' . ($path ? '/' . $path : '');
        }
    }

    try {
        $config = include $configPath;
        if (isset($config['connections']['mysql'])) {
            $mysqlConfig = $config['connections']['mysql'];
            echo "✅ MySQL connection configured\n";

            // Check for required database settings
            $required = ['host', 'database', 'username', 'password'];
            $missing = [];
            foreach ($required as $key) {
                if (!isset($mysqlConfig[$key]) || empty($mysqlConfig[$key])) {
                    $missing[] = $key;
                }
            }

            if (!empty($missing)) {
                $results['issues'][] = "Missing database configuration: " . implode(', ', $missing);
                echo "⚠️  Missing configuration: " . implode(', ', $missing) . "\n";
            } else {
                echo "✅ All required database settings present\n";
            }
        } else {
            $results['issues'][] = "MySQL connection not configured in database.php";
            echo "❌ MySQL connection not configured\n";
        }
    } catch (Exception $e) {
        $results['issues'][] = "Error loading database config: " . $e->getMessage();
        echo "❌ Error loading database config: " . $e->getMessage() . "\n";
    }
} else {
    $results['issues'][] = "Database config file not found";
    echo "❌ Database config file not found\n";
}

echo "\n";

// Check migration files
echo "📄 Analyzing Migration Files\n";
echo "===========================\n";

$migrationDir = $projectRoot . '/database/migrations';
if (is_dir($migrationDir)) {
    $migrationFiles = glob($migrationDir . '/*.php');
    echo "✅ Found " . count($migrationFiles) . " migration files\n";

    $tables = [];
    $issues = [];

    foreach ($migrationFiles as $file) {
        $content = file_get_contents($file);
        if ($content === false) continue;

        // Extract table name from migration
        if (preg_match('/create\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/', $content, $matches)) {
            $tableName = $matches[1];
            $tables[] = $tableName;
        }

        // Check for foreign key constraints
        if (preg_match_all('/foreign\s*\(/', $content, $fkMatches)) {
            // Foreign keys found - good
        }

        // Check for indexes
        if (preg_match_all('/index\s*\(/', $content, $indexMatches)) {
            // Indexes found - good
        }

        // Check for potential issues
        if (strpos($content, 'nullable()') === false && strpos($content, 'default(') === false) {
            // This might be too strict, but worth noting
        }
    }

    echo "📊 Tables to be created: " . count($tables) . "\n";
    $results['schema_analysis']['migration_tables'] = $tables;

    // Check for common table patterns
    $coreTables = ['users', 'agents', 'properties', 'leads'];
    $missingCore = [];
    foreach ($coreTables as $core) {
        if (!in_array($core, $tables) && !in_array($core . 's', $tables)) {
            $missingCore[] = $core;
        }
    }

    if (!empty($missingCore)) {
        $results['issues'][] = "Potentially missing core tables: " . implode(', ', $missingCore);
    }

} else {
    $results['issues'][] = "Migrations directory not found";
    echo "❌ Migrations directory not found\n";
}

echo "\n";

// Check model relationships
echo "🔗 Analyzing Model Relationships\n";
echo "===============================\n";

$modelDir = $projectRoot . '/app/Models';
if (is_dir($modelDir)) {
    $modelFiles = glob($modelDir . '/*.php');
    echo "✅ Found " . count($modelFiles) . " model files\n";

    $relationships = [];
    foreach ($modelFiles as $file) {
        $content = file_get_contents($file);
        if ($content === false) continue;

        $modelName = basename($file, '.php');
        $modelRelationships = [];

        // Check for relationship methods
        $relationPatterns = [
            'belongsTo' => '/function\s+\w+\s*\(\s*\)\s*\{\s*return\s*\$this\s*->\s*belongsTo\s*\(/',
            'hasMany' => '/function\s+\w+\s*\(\s*\)\s*\{\s*return\s*\$this\s*->\s*hasMany\s*\(/',
            'hasOne' => '/function\s+\w+\s*\(\s*\)\s*\{\s*return\s*\$this\s*->\s*hasOne\s*\(/',
            'belongsToMany' => '/function\s+\w+\s*\(\s*\)\s*\{\s*return\s*\$this\s*->\s*belongsToMany\s*\(/'
        ];

        foreach ($relationPatterns as $type => $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $modelRelationships[$type] = count($matches[0]);
            }
        }

        if (!empty($modelRelationships)) {
            $relationships[$modelName] = $modelRelationships;
        }
    }

    $modelsWithRelationships = count($relationships);
    echo "🔗 Models with relationships: {$modelsWithRelationships}\n";

    $results['relationship_analysis']['model_relationships'] = $relationships;

} else {
    $results['issues'][] = "Models directory not found";
    echo "❌ Models directory not found\n";
}

echo "\n";

// Check for database seeding files
echo "🌱 Checking Database Seeds\n";
echo "=========================\n";

$seedDir = $projectRoot . '/database/seeders';
if (is_dir($seedDir)) {
    $seedFiles = glob($seedDir . '/*.php');
    echo "✅ Found " . count($seedFiles) . " seeder files\n";

    foreach ($seedFiles as $file) {
        $filename = basename($file);
        echo "  📄 {$filename}\n";
    }
} else {
    echo "⚠️  Seeders directory not found\n";
    $results['issues'][] = "Database seeders directory not found - consider creating seeders for test data";
}

echo "\n";

// Check for factories (if using Laravel)
echo "🏭 Checking Model Factories\n";
echo "===========================\n";

$factoryDir = $projectRoot . '/database/factories';
if (is_dir($factoryDir)) {
    $factoryFiles = glob($factoryDir . '/*.php');
    echo "✅ Found " . count($factoryFiles) . " factory files\n";
} else {
    echo "ℹ️  Factories directory not found (not required for basic functionality)\n";
}

echo "\n";

// Performance and optimization analysis
echo "⚡ Database Performance Analysis\n";
echo "===============================\n";

$performance = [];

// Check migration file sizes (large migrations might indicate performance issues)
if (isset($migrationFiles)) {
    $largeMigrations = [];
    foreach ($migrationFiles as $file) {
        $size = filesize($file);
        if ($size > 10000) { // 10KB
            $largeMigrations[] = basename($file) . " (" . round($size/1024, 1) . "KB)";
        }
    }

    if (!empty($largeMigrations)) {
        $performance[] = "Large migration files detected: " . implode(', ', $largeMigrations);
        echo "⚠️  Large migration files may impact performance\n";
    }
}

// Check for proper indexing in migrations
$migrationContent = '';
foreach ($migrationFiles as $file) {
    $content = file_get_contents($file);
    if ($content) {
        $migrationContent .= $content;
    }
}

$indexCount = substr_count($migrationContent, '->index(');
$uniqueCount = substr_count($migrationContent, '->unique(');
$foreignKeyCount = substr_count($migrationContent, '->foreign(');

echo "📊 Database optimization metrics:\n";
echo "  🔍 Indexes: {$indexCount}\n";
echo "  🎯 Unique constraints: {$uniqueCount}\n";
echo "  🔗 Foreign keys: {$foreignKeyCount}\n";

if ($indexCount < count($migrationFiles) * 0.5) {
    $results['issues'][] = "Low index coverage - consider adding more indexes for better performance";
}

if ($foreignKeyCount === 0) {
    $results['issues'][] = "No foreign key constraints found - data integrity may be compromised";
}

$results['schema_analysis']['performance_metrics'] = [
    'indexes' => $indexCount,
    'unique_constraints' => $uniqueCount,
    'foreign_keys' => $foreignKeyCount,
    'performance_issues' => $performance
];

echo "\n";

// Generate summary
echo "📊 Analysis Summary\n";
echo "==================\n";

$totalIssues = count($results['issues']);
if ($totalIssues === 0) {
    echo "🎉 Database schema appears well-structured!\n";
} else {
    echo "⚠️  Found {$totalIssues} database-related issues:\n";
    foreach ($results['issues'] as $issue) {
        echo "   • {$issue}\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Run migrations: php artisan migrate\n";
echo "• Run migration status: php artisan migrate:status\n";
echo "• Create database seeders for test data\n";
echo "• Add proper indexes for frequently queried columns\n";
echo "• Ensure foreign key constraints are properly defined\n";
echo "• Consider database optimization and normalization\n";
echo "• Run Phase 6: Configuration file review\n";

$results['summary'] = [
    'status' => $totalIssues === 0 ? 'healthy' : 'needs_attention',
    'total_issues' => $totalIssues,
    'migration_files' => isset($migrationFiles) ? count($migrationFiles) : 0,
    'model_files' => isset($modelFiles) ? count($modelFiles) : 0
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase5_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 5 Complete - Ready for Phase 6!\n";

?>
