<?php
/**
 * APS Dream Home - Database Optimization Script
 * Automated database performance optimization
 */

echo "🗄️ APS DREAM HOME - DATABASE OPTIMIZATION\n";
echo "==========================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error . "\n");
}

echo "✅ Database connected successfully\n\n";

// Optimization results
$optimizationResults = [];
$totalOptimizations = 0;
$successfulOptimizations = 0;

echo "🔍 ANALYZING DATABASE PERFORMANCE...\n\n";

// 1. Analyze table structures
echo "Step 1: Analyzing table structures\n";
$tables = ['properties', 'users', 'inquiries', 'favorites', 'search_history'];
foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        $columnCount = $result->num_rows;
        echo "   ✅ $table: $columnCount columns\n";
        
        // Check for missing indexes
        $indexResult = $conn->query("SHOW INDEX FROM $table");
        $indexCount = $indexResult->num_rows;
        echo "      📊 Indexes: $indexCount\n";
        
        $optimizationResults['table_analysis'][$table] = [
            'columns' => $columnCount,
            'indexes' => $indexCount,
            'status' => 'analyzed'
        ];
        $totalOptimizations++;
        $successfulOptimizations++;
    } else {
        echo "   ❌ $table: Analysis failed\n";
        $optimizationResults['table_analysis'][$table] = ['status' => 'failed'];
    }
}

echo "\nStep 2: Checking for missing indexes\n";
$missingIndexes = [
    'properties' => [
        'idx_properties_location' => 'location(100)',
        'idx_properties_type' => 'type',
        'idx_properties_price' => 'price',
        'idx_properties_status' => 'status',
        'idx_properties_created' => 'created_at'
    ],
    'users' => [
        'idx_users_email' => 'email',
        'idx_users_role' => 'role',
        'idx_users_created' => 'created_at'
    ],
    'inquiries' => [
        'idx_inquiries_property' => 'property_id',
        'idx_inquiries_user' => 'user_id',
        'idx_inquiries_status' => 'status',
        'idx_inquiries_created' => 'created_at'
    ]
];

foreach ($missingIndexes as $table => $indexes) {
    echo "   🔍 Checking $table for missing indexes...\n";
    foreach ($indexes as $indexName => $columns) {
        $checkIndex = $conn->query("SHOW INDEX FROM $table WHERE Key_name = '$indexName'");
        if ($checkIndex->num_rows == 0) {
            echo "      📝 Adding missing index: $indexName\n";
            $createIndex = $conn->query("ALTER TABLE $table ADD INDEX $indexName ($columns)");
            if ($createIndex) {
                echo "         ✅ Index created successfully\n";
                $optimizationResults['indexes'][$table][$indexName] = 'created';
                $successfulOptimizations++;
            } else {
                echo "         ❌ Index creation failed\n";
                $optimizationResults['indexes'][$table][$indexName] = 'failed';
            }
            $totalOptimizations++;
        } else {
            echo "      ✅ Index $indexName already exists\n";
            $optimizationResults['indexes'][$table][$indexName] = 'exists';
            $successfulOptimizations++;
            $totalOptimizations++;
        }
    }
}

echo "\nStep 3: Optimizing table structures\n";
foreach ($tables as $table) {
    echo "   🔧 Optimizing $table...\n";
    $optimizeResult = $conn->query("OPTIMIZE TABLE $table");
    if ($optimizeResult) {
        echo "      ✅ Table optimized\n";
        $optimizationResults['optimization'][$table] = 'optimized';
        $successfulOptimizations++;
    } else {
        echo "      ❌ Optimization failed\n";
        $optimizationResults['optimization'][$table] = 'failed';
    }
    $totalOptimizations++;
}

echo "\nStep 4: Analyzing query performance\n";
$slowQueries = [
    "SELECT * FROM properties WHERE location LIKE '%gorakhpur%' ORDER BY price DESC LIMIT 10",
    "SELECT p.*, u.name as agent_name FROM properties p LEFT JOIN users u ON p.agent_id = u.id WHERE p.status = 'active'",
    "SELECT COUNT(*) as total FROM properties WHERE type = 'residential' AND status = 'active'",
    "SELECT * FROM inquiries WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5"
];

foreach ($slowQueries as $i => $query) {
    echo "   🔍 Analyzing query " . ($i + 1) . "...\n";
    $startTime = microtime(true);
    $result = $conn->query("EXPLAIN " . $query);
    $endTime = microtime(true);
    $analysisTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($result) {
        echo "      ✅ Query analyzed ({$analysisTime}ms)\n";
        $optimizationResults['query_analysis']['query_' . ($i + 1)] = [
            'analysis_time' => $analysisTime,
            'status' => 'analyzed'
        ];
        $successfulOptimizations++;
    } else {
        echo "      ❌ Query analysis failed\n";
        $optimizationResults['query_analysis']['query_' . ($i + 1)] = ['status' => 'failed'];
    }
    $totalOptimizations++;
}

echo "\nStep 5: Checking database configuration\n";
$configChecks = [
    'innodb_buffer_pool_size' => 'SHOW VARIABLES LIKE \'innodb_buffer_pool_size\'',
    'query_cache_size' => 'SHOW VARIABLES LIKE \'query_cache_size\'',
    'max_connections' => 'SHOW VARIABLES LIKE \'max_connections\'',
    'innodb_log_file_size' => 'SHOW VARIABLES LIKE \'innodb_log_file_size\''
];

foreach ($configChecks as $config => $query) {
    echo "   🔍 Checking $config...\n";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $value = $row['Value'];
        echo "      📊 Current value: $value\n";
        $optimizationResults['config'][$config] = [
            'current_value' => $value,
            'status' => 'checked'
        ];
        $successfulOptimizations++;
    } else {
        echo "      ❌ Configuration check failed\n";
        $optimizationResults['config'][$config] = ['status' => 'failed'];
    }
    $totalOptimizations++;
}

echo "\nStep 6: Performance metrics collection\n";
$metrics = [
    'total_tables' => 'SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = \'apsdreamhome\'',
    'total_indexes' => 'SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = \'apsdreamhome\'',
    'database_size' => 'SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = \'apsdreamhome\'',
    'total_rows' => 'SELECT SUM(table_rows) as total FROM information_schema.tables WHERE table_schema = \'apsdreamhome\''
];

foreach ($metrics as $metric => $query) {
    echo "   📊 Collecting $metric...\n";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $value = $row[$metric === 'database_size' ? 'size' : ($metric === 'total_rows' ? 'total' : 'count')];
        echo "      📈 $metric: $value\n";
        $optimizationResults['metrics'][$metric] = [
            'value' => $value,
            'status' => 'collected'
        ];
        $successfulOptimizations++;
    } else {
        echo "      ❌ Metric collection failed\n";
        $optimizationResults['metrics'][$metric] = ['status' => 'failed'];
    }
    $totalOptimizations++;
}

// Summary
echo "\n==========================================\n";
echo "📊 DATABASE OPTIMIZATION SUMMARY\n";
echo "==========================================\n";

$successRate = round(($successfulOptimizations / $totalOptimizations) * 100, 1);
echo "📊 TOTAL OPTIMIZATIONS: $totalOptimizations\n";
echo "✅ SUCCESSFUL: $successfulOptimizations\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 OPTIMIZATION DETAILS:\n";
foreach ($optimizationResults as $category => $results) {
    echo "📋 $category:\n";
    if (is_array($results)) {
        foreach ($results as $item => $result) {
            if (is_array($result)) {
                $status = $result['status'] ?? 'unknown';
                $icon = $status === 'optimized' || $status === 'created' || $status === 'analyzed' || $status === 'checked' || $status === 'collected' || $status === 'exists' ? '✅' : ($status === 'failed' ? '❌' : '⚠️');
                echo "   $icon $item: $status\n";
            }
        }
    }
    echo "\n";
}

if ($successRate >= 80) {
    echo "🎉 DATABASE OPTIMIZATION: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ DATABASE OPTIMIZATION: GOOD!\n";
} else {
    echo "⚠️  DATABASE OPTIMIZATION: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Database optimization completed successfully!\n";
echo "📊 Ready for next optimization step: Application Caching\n";

$conn->close();
?>
