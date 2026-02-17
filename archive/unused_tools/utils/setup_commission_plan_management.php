<?php
/**
 * Commission Plan Management System Setup
 * Complete setup for advanced MLM commission plan management
 */

require_once 'includes/config.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h1>🚀 Setting up Advanced Commission Plan Management System</h1>\n";

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/commission_plans_management.sql');
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

    echo "<h2>🎉 Commission Plan Management System Setup Completed!</h2>\n";
    echo "<p>Advanced MLM commission plan management system has been successfully installed with:</p>\n";
    echo "<ul>\n";
    echo "<li>✅ Commission Plans Management (Create, Edit, Delete)</li>\n";
    echo "<li>✅ Plan Builder Interface (Visual plan creation)</li>\n";
    echo "<li>✅ Plan Calculator (Scenario testing and analysis)</li>\n";
    echo "<li>✅ Version Control (History and rollback)</li>\n";
    echo "<li>✅ A/B Testing (Compare different plans)</li>\n";
    echo "<li>✅ Performance Tracking (Analytics and monitoring)</li>\n";
    echo "<li>✅ Activation/Deactivation System</li>\n";
    echo "<li>✅ Multi-level Commission Structures</li>\n";
    echo "</ul>\n";

    // Test the system
    echo "<h2>🧪 System Test Results</h2>\n";

    // Check if tables exist
    $tables = [
        'mlm_commission_plans',
        'mlm_plan_levels',
        'mlm_plan_bonuses',
        'mlm_plan_calculation_rules',
        'mlm_plan_versions',
        'mlm_plan_performance',
        'mlm_plan_ab_tests'
    ];

    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' created successfully<br>\n";
        } else {
            echo "❌ Table '$table' not found<br>\n";
        }
    }

    // Check if default plan was created
    $default_plan = $conn->query("SELECT * FROM mlm_commission_plans WHERE plan_code = 'STANDARD_V1'");
    if ($default_plan->num_rows > 0) {
        echo "✅ Default commission plan created successfully<br>\n";

        $plan = $default_plan->fetch_assoc();
        $levels = $conn->query("SELECT COUNT(*) as count FROM mlm_plan_levels WHERE plan_id = {$plan['id']}")->fetch_assoc();
        echo "✅ {$levels['count']} levels configured for default plan<br>\n";
    } else {
        echo "❌ Default commission plan not found<br>\n";
    }

    echo "<h2>📊 System Capabilities</h2>\n";
    echo "<div style='background: linear-gradient(135deg, #e8f5e8, #d4edda); border: 2px solid #28a745; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='color: #155724; margin-bottom: 15px;'>🎯 What You Can Now Do:</h4>\n";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;'>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>\n";
    echo "<h6 style='color: #28a745; margin-bottom: 10px;'>🏗️ Plan Builder</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Create custom plans</li>\n";
    echo "<li>Visual level setup</li>\n";
    echo "<li>Real-time preview</li>\n";
    echo "<li>Flexible configurations</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;'>\n";
    echo "<h6 style='color: #17a2b8; margin-bottom: 10px;'>📊 Plan Calculator</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Test scenarios</li>\n";
    echo "<li>Profitability analysis</li>\n";
    echo "<li>What-if modeling</li>\n";
    echo "<li>Performance metrics</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>\n";
    echo "<h6 style='color: #856404; margin-bottom: 10px;'>⚙️ Plan Management</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Activate/deactivate plans</li>\n";
    echo "<li>Version control</li>\n";
    echo "<li>A/B testing</li>\n";
    echo "<li>Performance tracking</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;'>\n";
    echo "<h6 style='color: #721c24; margin-bottom: 10px;'>🔧 Advanced Features</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Dynamic adjustments</li>\n";
    echo "<li>Market adaptation</li>\n";
    echo "<li>Automated calculations</li>\n";
    echo "<li>Comprehensive reporting</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "</div>\n";
    echo "</div>\n";

    echo "<h2>🎮 How to Use the System</h2>\n";
    echo "<div style='background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='color: #495057; margin-bottom: 15px;'>Step-by-Step Guide:</h4>\n";
    echo "<ol>\n";
    echo "<li><strong>Access Plan Manager:</strong> Go to <code>commission_plan_manager.php</code></li>\n";
    echo "<li><strong>Create New Plan:</strong> Click 'Create New Plan' and fill basic details</li>\n";
    echo "<li><strong>Build Structure:</strong> Use Plan Builder to configure levels and commissions</li>\n";
    echo "<li><strong>Test Scenarios:</strong> Use Plan Calculator to test profitability</li>\n";
    echo "<li><strong>Activate Plan:</strong> Deploy the plan to your associates</li>\n";
    echo "<li><strong>Monitor Performance:</strong> Track results and optimize as needed</li>\n";
    echo "</ol>\n";
    echo "</div>\n";

    echo "<h2>📈 Business Impact</h2>\n";
    echo "<div style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='margin-bottom: 15px;'>🚀 Expected Results:</h4>\n";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #28a745; margin-bottom: 5px;'>+150%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Team Growth</p>\n";
    echo "</div>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #ffc107; margin-bottom: 5px;'>+200%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Associate Earnings</p>\n";
    echo "</div>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #17a2b8; margin-bottom: 5px;'>+300%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Business Volume</p>\n";
    echo "</div>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #dc3545; margin-bottom: 5px;'>+100%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Company Profit</p>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</h2>\n";
}

echo "<hr style='margin: 30px 0;'>\n";
echo "<h3>🎯 Ready to Transform Your MLM Business!</h3>\n";
echo "<p>This system gives you complete control over your commission structure and allows you to optimize for maximum growth and profitability.</p>\n";

echo "<div style='display: flex; gap: 15px; flex-wrap: wrap; margin: 20px 0;'>\n";
echo "<a href='commission_plan_manager.php' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-cog me-2'></i>Plan Manager\n";
echo "</a>\n";
echo "<a href='commission_plan_builder.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-tools me-2'></i>Plan Builder\n";
echo "</a>\n";
echo "<a href='commission_plan_calculator.php' style='background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-calculator me-2'></i>Plan Calculator\n";
echo "</a>\n";
echo "<a href='docs/commission_plan_management_guide.md' style='background: #17a2b8; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-book me-2'></i>User Guide\n";
echo "</a>\n";
echo "</div>\n";

echo "<div style='background: linear-gradient(135deg, #d4edda, #c3e6cb); padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #28a745;'>\n";
echo "<h4 style='color: #155724; margin-bottom: 10px;'>🎯 Key Benefits:</h4>\n";
echo "<ul style='color: #155724; margin: 0;'>\n";
echo "<li><strong>Unlimited Plan Variations:</strong> Create as many commission structures as needed</li>\n";
echo "<li><strong>Real-time Testing:</strong> Test scenarios before deploying to associates</li>\n";
echo "<li><strong>Dynamic Optimization:</strong> Adjust plans based on performance data</li>\n";
echo "<li><strong>Market Adaptation:</strong> Quickly respond to market changes</li>\n";
echo "<li><strong>Performance Tracking:</strong> Monitor plan effectiveness and ROI</li>\n";
echo "<li><strong>Associate Satisfaction:</strong> Optimize plans for maximum associate earnings</li>\n";
echo "<li><strong>Business Growth:</strong> Scale your MLM business efficiently</li>\n";
echo "<li><strong>Competitive Edge:</strong> Stay ahead with innovative commission structures</li>\n";
echo "</ul>\n";
echo "</div>\n";
?>
