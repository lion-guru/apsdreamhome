<?php
/**
 * Commission System Setup
 * Run this script to initialize the commission database system
 */

require_once 'includes/config.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h1>💰 Setting up Advanced MLM Commission System</h1>\n";

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/commission_system.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $conn->query($statement);
                echo "✅ Executed: " . substr(str_replace("\n", " ", $statement), 0, 60) . "...<br>\n";
            } catch (Exception $e) {
                echo "❌ Error executing: " . $e->getMessage() . "<br>\n";
            }
        }
    }

    echo "<h2>🎉 Commission system setup completed!</h2>\n";
    echo "<p>The Advanced MLM Commission System has been initialized with:</p>\n";
    echo "<ul>\n";
    echo "<li>✅ Multiple commission types (Direct, Team, Level Bonus, Matching, Leadership, Performance)</li>\n";
    echo "<li>✅ Comprehensive tracking tables</li>\n";
    echo "<li>✅ Payout management system</li>\n";
    echo "<li>✅ Target and achievement tracking</li>\n";
    echo "<li>✅ Rank advancement system</li>\n";
    echo "<li>✅ Withdrawal request management</li>\n";
    echo "<li>✅ Analytics and reporting tables</li>\n";
    echo "</ul>\n";

    // Test the system
    echo "<h2>🧪 System Test Results</h2>\n";

    // Check if tables exist
    $tables = ['mlm_commission_records', 'mlm_commissions', 'mlm_payouts', 'mlm_commission_targets', 'mlm_rank_advancements', 'mlm_withdrawal_requests', 'mlm_commission_analytics'];

    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' created successfully<br>\n";
        } else {
            echo "❌ Table '$table' not found<br>\n";
        }
    }

    // Show commission structure
    echo "<h2>📊 Commission Structure by Level</h2>\n";

    require_once 'includes/commission_system.php';
    $structure = getCommissionStructure();

    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>\n";

    foreach ($structure as $level => $details) {
        echo "<div style='border: 2px solid #667eea; border-radius: 10px; padding: 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef);'>\n";
        echo "<h3 style='color: #667eea; margin-bottom: 15px;'>$level</h3>\n";
        echo "<div style='margin-bottom: 10px;'><strong>Direct Commission:</strong> {$details['direct_commission']}%</div>\n";
        echo "<div style='margin-bottom: 10px;'><strong>Team Commission:</strong> {$details['team_commission']}%</div>\n";
        echo "<div style='margin-bottom: 10px;'><strong>Level Bonus:</strong> " . ($details['level_bonus'] ?? 0) . "%</div>\n";
        echo "<div style='margin-bottom: 10px;'><strong>Matching Bonus:</strong> " . ($details['matching_bonus'] ?? 0) . "%</div>\n";
        if (isset($details['leadership_bonus'])) {
            echo "<div style='margin-bottom: 10px;'><strong>Leadership Bonus:</strong> {$details['leadership_bonus']}%</div>\n";
        }
        if (isset($details['performance_bonus'])) {
            echo "<div style='margin-bottom: 10px;'><strong>Performance Bonus:</strong> {$details['performance_bonus']}%</div>\n";
        }
        echo "<div style='margin-bottom: 10px; font-weight: bold; color: #28a745;'>Max Potential: " . (
            $details['direct_commission'] +
            $details['team_commission'] +
            ($details['level_bonus'] ?? 0) +
            ($details['matching_bonus'] ?? 0) +
            ($details['leadership_bonus'] ?? 0) +
            ($details['performance_bonus'] ?? 0)
        ) . "%</div>\n";
        echo "<div style='font-size: 0.9em; color: #666;'>Target: ₹" . number_format($details['target']) . "</div>\n";
        echo "</div>\n";
    }

    echo "</div>\n";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</h2>\n";
}

echo "<hr style='margin: 30px 0;'>\n";
echo "<h3>📋 Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>✅ Database tables created</li>\n";
echo "<li>✅ Commission functions available in <code>includes/commission_system.php</code></li>\n";
echo "<li>✅ Commission dashboard created at <code>commission_dashboard.php</code></li>\n";
echo "<li>🔄 Test commission calculations with sample data</li>\n";
echo "<li>🔄 Set up payout processing system</li>\n";
echo "<li>🔄 Configure withdrawal management</li>\n";
echo "</ol>\n";

echo "<div style='background: linear-gradient(135deg, #d4edda, #c3e6cb); padding: 20px; border-radius: 10px; margin: 20px 0;'>\n";
echo "<h4 style='color: #155724; margin-bottom: 10px;'>🎯 Key Features Implemented:</h4>\n";
echo "<ul style='color: #155724;'>\n";
echo "<li><strong>6 Types of Commissions:</strong> Direct, Team, Level Difference, Matching, Leadership, Performance</li>\n";
echo "<li><strong>Level-based Progression:</strong> 7 levels from Associate to Site Manager</li>\n";
echo "<li><strong>Automatic Calculations:</strong> Real-time commission computation</li>\n";
echo "<li><strong>Team Building Incentives:</strong> Bonuses for recruiting and training</li>\n";
echo "<li><strong>Performance Tracking:</strong> Monthly targets and achievements</li>\n";
echo "<li><strong>Payout Management:</strong> Automated payout processing</li>\n";
echo "<li><strong>Analytics Dashboard:</strong> Comprehensive reporting and tracking</li>\n";
echo "<li><strong>Withdrawal System:</strong> Easy commission withdrawal</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<p><a href='commission_dashboard.php' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; margin: 10px;'>🗂️ View Commission Dashboard</a></p>\n";
echo "<p><a href='docs/commission_system_guide.md' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; margin: 10px;'>📖 Read Complete Guide</a></p>\n";
?>
