<?php
/**
 * APS Dream Home - Database Index Optimization
 * Analyze database schema and add missing indexes for performance
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'database_index_optimization',
    'current_indexes' => [],
    'missing_indexes' => [],
    'migration_created' => false,
    'recommendations' => []
];

echo "🗄️ DATABASE INDEX OPTIMIZATION\n";
echo "==============================\n\n";

// Function to analyze model relationships for index needs
function analyzeModelRelationships($modelFile, &$results) {
    if (!file_exists($modelFile) || !is_readable($modelFile)) {
        return false;
    }

    $content = file_get_contents($modelFile);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $modelFile);
    $tableName = strtolower(basename($modelFile, '.php'));

    // Extract relationships
    $relationships = [];

    // BelongsTo relationships
    preg_match_all('/public\s+function\s+(\w+)\s*\(\s*\)\s*\{.*?belongsTo\s*\(\s*[\'"](\w+)[\'"]/', $content, $belongsToMatches);
    foreach ($belongsToMatches[1] as $index => $method) {
        $relatedModel = $belongsToMatches[2][$index];
        $foreignKey = strtolower($relatedModel) . '_id';
        $relationships[] = [
            'type' => 'belongsTo',
            'method' => $method,
            'foreign_key' => $foreignKey,
            'related_table' => strtolower($relatedModel) . 's'
        ];
    }

    // HasMany relationships
    preg_match_all('/public\s+function\s+(\w+)\s*\(\s*\)\s*\{.*?hasMany\s*\(\s*[\'"](\w+)[\'"]/', $content, $hasManyMatches);
    foreach ($hasManyMatches[1] as $index => $method) {
        $relatedModel = $hasManyMatches[2][$index];
        $foreignKey = $tableName . '_id';
        $relationships[] = [
            'type' => 'hasMany',
            'method' => $method,
            'foreign_key' => $foreignKey,
            'related_table' => strtolower($relatedModel) . 's'
        ];
    }

    // BelongsToMany relationships
    preg_match_all('/public\s+function\s+(\w+)\s*\(\s*\)\s*\{.*?belongsToMany\s*\(\s*[\'"](\w+)[\'"]/', $content, $belongsToManyMatches);
    foreach ($belongsToManyMatches[1] as $index => $method) {
        $relatedModel = $belongsToManyMatches[2][$index];
        $relationships[] = [
            'type' => 'belongsToMany',
            'method' => $method,
            'pivot_table' => $tableName . '_' . strtolower($relatedModel) . 's',
            'related_table' => strtolower($relatedModel) . 's'
        ];
    }

    return $relationships;
}

// Function to suggest indexes based on relationships and common patterns
function suggestIndexes($tableName, $relationships) {
    $indexes = [];

    // Foreign key indexes
    foreach ($relationships as $relationship) {
        if (in_array($relationship['type'], ['belongsTo', 'hasMany'])) {
            $indexes[] = [
                'type' => 'foreign_key',
                'columns' => [$relationship['foreign_key']],
                'name' => $tableName . '_' . $relationship['foreign_key'] . '_index'
            ];
        }
    }

    // Common query pattern indexes
    $commonPatterns = [
        'users' => [
            ['columns' => ['email'], 'name' => 'users_email_index'],
            ['columns' => ['status'], 'name' => 'users_status_index'],
            ['columns' => ['created_at'], 'name' => 'users_created_at_index']
        ],
        'properties' => [
            ['columns' => ['agent_id', 'status'], 'name' => 'properties_agent_status_index'],
            ['columns' => ['featured'], 'name' => 'properties_featured_index'],
            ['columns' => ['created_at'], 'name' => 'properties_created_at_index']
        ],
        'leads' => [
            ['columns' => ['agent_id', 'status'], 'name' => 'leads_agent_status_index'],
            ['columns' => ['priority'], 'name' => 'leads_priority_index'],
            ['columns' => ['created_at'], 'name' => 'leads_created_at_index']
        ],
        'commissions' => [
            ['columns' => ['associate_id', 'status'], 'name' => 'commissions_associate_status_index'],
            ['columns' => ['created_at'], 'name' => 'commissions_created_at_index']
        ],
        'payouts' => [
            ['columns' => ['associate_id', 'status'], 'name' => 'payouts_associate_status_index'],
            ['columns' => ['created_at'], 'name' => 'payouts_created_at_index']
        ],
        'messages' => [
            ['columns' => ['conversation_id'], 'name' => 'messages_conversation_id_index'],
            ['columns' => ['sender_id'], 'name' => 'messages_sender_id_index'],
            ['columns' => ['created_at'], 'name' => 'messages_created_at_index']
        ],
        'email_tracking' => [
            ['columns' => ['user_id'], 'name' => 'email_tracking_user_id_index'],
            ['columns' => ['status'], 'name' => 'email_tracking_status_index']
        ]
    ];

    if (isset($commonPatterns[$tableName])) {
        $indexes = array_merge($indexes, $commonPatterns[$tableName]);
    }

    return $indexes;
}

// Analyze models for relationships and index needs
echo "🔍 Analyzing Models for Index Requirements\n";
echo "==========================================\n";

$modelDir = $projectRoot . '/app/Models';
$models = glob($modelDir . '/*.php');

$allIndexes = [];

foreach ($models as $modelFile) {
    $modelName = basename($modelFile, '.php');
    $tableName = strtolower($modelName) . 's'; // Basic pluralization

    echo "📋 Analyzing {$modelName} model\n";

    $relationships = analyzeModelRelationships($modelFile, $results);
    $suggestedIndexes = suggestIndexes($tableName, $relationships);

    if (!empty($suggestedIndexes)) {
        $allIndexes[$tableName] = $suggestedIndexes;
        echo "   🔗 Found " . count($relationships) . " relationships\n";
        echo "   📊 Suggested " . count($suggestedIndexes) . " indexes\n";
    }
}

// Create migration for missing indexes
echo "\n📝 Creating Database Migration for Indexes\n";
echo "===========================================\n";

$migrationName = 'add_performance_indexes_' . date('Y_m_d_H_i_s');
$migrationFile = $projectRoot . '/database/migrations/' . date('Y_m_d_H_i_s') . '_' . $migrationName . '.php';

$migrationContent = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class " . str_replace('_', '', ucwords($migrationName, '_')) . " extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
";

foreach ($allIndexes as $table => $indexes) {
    $migrationContent .= "\n        // Indexes for {$table} table\n";
    foreach ($indexes as $index) {
        if ($index['type'] === 'foreign_key') {
            $migrationContent .= "        Schema::table('{$table}', function (Blueprint \$table) {\n";
            $migrationContent .= "            \$table->index('{$index['columns'][0]}');\n";
            $migrationContent .= "        });\n";
        } else {
            $columnsStr = "['" . implode("', '", $index['columns']) . "']";
            $migrationContent .= "        Schema::table('{$table}', function (Blueprint \$table) {\n";
            $migrationContent .= "            \$table->index({$columnsStr}, '{$index['name']}');\n";
            $migrationContent .= "        });\n";
        }
    }
}

$migrationContent .= "    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
";

foreach ($allIndexes as $table => $indexes) {
    $migrationContent .= "\n        // Drop indexes for {$table} table\n";
    foreach ($indexes as $index) {
        $migrationContent .= "        Schema::table('{$table}', function (Blueprint \$table) {\n";
        if ($index['type'] === 'foreign_key') {
            $migrationContent .= "            \$table->dropIndex('{$index['columns'][0]}');\n";
        } else {
            $migrationContent .= "            \$table->dropIndex('{$index['name']}');\n";
        }
        $migrationContent .= "        });\n";
    }
}

$migrationContent .= "    }
}
";

if (file_put_contents($migrationFile, $migrationContent)) {
    $results['migration_created'] = true;
    $results['migration_file'] = str_replace($projectRoot . '/', '', $migrationFile);
    echo "✅ Migration created: {$migrationFile}\n";
} else {
    echo "❌ Failed to create migration file\n";
}

// Generate summary
echo "\n📊 Index Optimization Summary\n";
echo "=============================\n";
echo "📋 Tables analyzed: " . count($allIndexes) . "\n";
echo "📊 Total indexes suggested: " . array_sum(array_map('count', $allIndexes)) . "\n";
echo "📝 Migration created: " . ($results['migration_created'] ? 'Yes' : 'No') . "\n";

if (!empty($allIndexes)) {
    echo "\n🗂️ Index Details\n";
    echo "===============\n";
    foreach ($allIndexes as $table => $indexes) {
        echo "📁 {$table}:\n";
        foreach ($indexes as $index) {
            echo "   🔍 " . implode(', ', $index['columns']) . " (" . $index['type'] . ")\n";
        }
        echo "\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Run the migration to add the suggested indexes\n";
echo "• Monitor query performance before and after index addition\n";
echo "• Consider composite indexes for frequently used query combinations\n";
echo "• Use EXPLAIN to analyze query execution plans\n";
echo "• Avoid over-indexing as it can slow down writes\n";
echo "• 🔄 Next: Complete missing configuration and environment setups\n";

$results['summary'] = [
    'tables_analyzed' => count($allIndexes),
    'total_indexes_suggested' => array_sum(array_map('count', $allIndexes)),
    'migration_created' => $results['migration_created']
];

// Save results
$resultsFile = $projectRoot . '/database_index_optimization.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Database index optimization completed!\n";

?>
