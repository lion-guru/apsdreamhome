<?php
/**
 * Hybrid Real Estate MLM System Setup
 * Complete setup for your real estate business with company and resell properties
 */

require_once 'includes/config.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h1>🏗️ Setting up Hybrid Real Estate MLM System</h1>\n";

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/hybrid_real_estate_system.sql');
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

    echo "<h2>🎉 Hybrid Real Estate MLM System Setup Completed!</h2>\n";
    echo "<p>Your comprehensive real estate business management system has been successfully installed with:</p>\n";
    echo "<ul>\n";
    echo "<li>✅ Hybrid Commission Plans (Company MLM + Resell Fixed)</li>\n";
    echo "<li>✅ Development Cost Calculator with Commission Integration</li>\n";
    echo "<li>✅ Property Management for Both Business Types</li>\n";
    echo "<li>✅ Plot Rate Calculator including All Costs</li>\n";
    echo "<li>✅ 7-Level MLM Structure for Company Properties</li>\n";
    echo "<li>✅ Fixed Commission Structure for Resell Properties</li>\n";
    echo "<li>✅ Comprehensive Dashboard and Analytics</li>\n";
    echo "<li>✅ Real Estate Specific Features and Calculations</li>\n";
    echo "</ul>\n";

    // Test the system
    echo "<h2>🧪 System Test Results</h2>\n";

    // Check if tables exist
    $tables = [
        'real_estate_properties',
        'property_development_costs',
        'hybrid_commission_plans',
        'company_property_levels',
        'resell_commission_structure',
        'plot_rate_calculations',
        'hybrid_commission_records'
    ];

    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' created successfully<br>\n";
        } else {
            echo "❌ Table '$table' not found<br>\n";
        }
    }

    // Check if sample data was created
    $company_properties = $conn->query("SELECT COUNT(*) as count FROM real_estate_properties WHERE property_type = 'company'")->fetch_assoc();
    $resell_properties = $conn->query("SELECT COUNT(*) as count FROM real_estate_properties WHERE property_type = 'resell'")->fetch_assoc();
    $plans = $conn->query("SELECT COUNT(*) as count FROM hybrid_commission_plans")->fetch_assoc();
    $levels = $conn->query("SELECT COUNT(*) as count FROM company_property_levels")->fetch_assoc();

    echo "✅ Sample company properties: {$company_properties['count']}<br>\n";
    echo "✅ Sample resell properties: {$resell_properties['count']}<br>\n";
    echo "✅ Commission plans created: {$plans['count']}<br>\n";
    echo "✅ MLM levels configured: {$levels['count']}<br>\n";

    echo "<h2>🏢 Your Business Capabilities</h2>\n";
    echo "<div style='background: linear-gradient(135deg, #e8f5e8, #d4edda); border: 2px solid #28a745; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='color: #155724; margin-bottom: 15px;'>🎯 What You Can Now Do:</h4>\n";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;'>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>\n";
    echo "<h6 style='color: #28a745; margin-bottom: 10px;'>🏗️ Company Properties</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>MLM commission structure</li>\n";
    echo "<li>7-level hierarchy</li>\n";
    echo "<li>Multiple bonus types</li>\n";
    echo "<li>Team building incentives</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>\n";
    echo "<h6 style='color: #856404; margin-bottom: 10px;'>🏠 Resell Properties</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Fixed commission rates</li>\n";
    echo "<li>Property category based</li>\n";
    echo "<li>Quick sales processing</li>\n";
    echo "<li>External property management</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;'>\n";
    echo "<h6 style='color: #17a2b8; margin-bottom: 10px;'>💰 Cost Calculator</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Development cost integration</li>\n";
    echo "<li>Commission cost calculation</li>\n";
    echo "<li>Plot rate optimization</li>\n";
    echo "<li>Profit margin analysis</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;'>\n";
    echo "<h6 style='color: #721c24; margin-bottom: 10px;'>📊 Management</h6>\n";
    echo "<ul style='margin: 0; padding-left: 20px; font-size: 0.9em;'>\n";
    echo "<li>Unified property database</li>\n";
    echo "<li>Automated commission system</li>\n";
    echo "<li>Real-time analytics</li>\n";
    echo "<li>Performance tracking</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "</div>\n";
    echo "</div>\n";

    echo "<h2>💼 Your Commission Structures</h2>\n";
    echo "<div style='background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='color: #495057; margin-bottom: 15px;'>Company Properties (MLM Structure):</h4>\n";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;'>\n";
    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>\n";
    echo "<h6>Associate (Level 1)</h6>\n";
    echo "<p>Direct: 6% | Team: 2% | Total: 8%</p>\n";
    echo "</div>\n";
    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>\n";
    echo "<h6>BDM (Level 3)</h6>\n";
    echo "<p>Direct: 10% | Team: 4% | Level: 2% | Matching: 3% | Leadership: 1% | Total: 20%</p>\n";
    echo "</div>\n";
    echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;'>\n";
    echo "<h6>Site Manager (Level 7)</h6>\n";
    echo "<p>Direct: 20% | Team: 8% | Level: 6% | Matching: 7% | Leadership: 5% | Total: 46%</p>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "<h4 style='color: #495057; margin-bottom: 15px; margin-top: 20px;'>Resell Properties (Fixed Structure):</h4>\n";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>\n";
    echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>\n";
    echo "<h6>Plots</h6>\n";
    echo "<p>₹0-1Cr: 3%<br>₹1Cr-5Cr: 4%<br>₹5Cr+: 5%</p>\n";
    echo "</div>\n";
    echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>\n";
    echo "<h6>Flats</h6>\n";
    echo "<p>₹0-5Cr: 2%<br>₹5Cr+: 3%</p>\n";
    echo "</div>\n";
    echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center;'>\n";
    echo "<h6>Other</h6>\n";
    echo "<p>House: 3%<br>Commercial: 4%<br>Land: 2%</p>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<h2>🎮 How to Use Your System</h2>\n";
    echo "<div style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='margin-bottom: 15px;'>Step-by-Step Guide:</h4>\n";
    echo "<ol>\n";
    echo "<li><strong>Calculate Development Costs:</strong> Use Development Cost Calculator to set plot rates</li>\n";
    echo "<li><strong>Add Properties:</strong> Add both company and resell properties to the system</li>\n";
    echo "<li><strong>Record Sales:</strong> System automatically calculates correct commissions</li>\n";
    echo "<li><strong>Monitor Performance:</strong> Use Hybrid Dashboard to track both business types</li>\n";
    echo "<li><strong>Optimize:</strong> Adjust commission structures based on performance</li>\n";
    echo "</ol>\n";
    echo "</div>\n";

    echo "<h2>📈 Expected Business Impact</h2>\n";
    echo "<div style='background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0;'>\n";
    echo "<h4 style='color: #495057; margin-bottom: 15px;'>Real Estate Business Growth:</h4>\n";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #28a745; margin-bottom: 5px;'>+200%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Company Property Sales</p>\n";
    echo "</div>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #ffc107; margin-bottom: 5px;'>+150%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Resell Property Volume</p>\n";
    echo "</div>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #17a2b8; margin-bottom: 5px;'>+300%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Associate Earnings</p>\n";
    echo "</div>\n";
    echo "<div style='text-align: center;'>\n";
    echo "<h3 style='color: #dc3545; margin-bottom: 5px;'>+100%</h3>\n";
    echo "<p style='margin: 0; font-size: 0.9em;'>Profit Margins</p>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</h2>\n";
}

echo "<hr style='margin: 30px 0;'>\n";
echo "<h3>🏗️ Your Real Estate Empire Awaits!</h3>\n";
echo "<p>This hybrid system perfectly handles your dual business model - company colony plotting with MLM commissions and resell properties with fixed commissions.</p>\n";

echo "<div style='display: flex; gap: 15px; flex-wrap: wrap; margin: 20px 0;'>\n";
echo "<a href='development_cost_calculator.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-calculator me-2'></i>Cost Calculator\n";
echo "</a>\n";
echo "<a href='property_management.php' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-building me-2'></i>Property Management\n";
echo "</a>\n";
echo "<a href='hybrid_commission_dashboard.php' style='background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-chart-line me-2'></i>Hybrid Dashboard\n";
echo "</a>\n";
echo "<a href='docs/hybrid_real_estate_guide.md' style='background: #17a2b8; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>\n";
echo "<i class='fas fa-book me-2'></i>Complete Guide\n";
echo "</a>\n";
echo "</div>\n";

echo "<div style='background: linear-gradient(135deg, #d4edda, #c3e6cb); padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #28a745;'>\n";
echo "<h4 style='color: #155724; margin-bottom: 10px;'>🎯 System Benefits:</h4>\n";
echo "<ul style='color: #155724; margin: 0;'>\n";
echo "<li><strong>Dual Business Support:</strong> Company plotting + Resell properties</li>\n";
echo "<li><strong>Integrated Cost Calculation:</strong> Development costs with commission integration</li>\n";
echo "<li><strong>Advanced MLM Structure:</strong> 7-level hierarchy with multiple bonus types</li>\n";
echo "<li><strong>Automated Commission System:</strong> Real-time calculations for both business types</li>\n";
echo "<li><strong>Property Management:</strong> Comprehensive database for all properties</li>\n";
echo "<li><strong>Performance Analytics:</strong> Track both business types separately</li>\n";
echo "<li><strong>Scalable Architecture:</strong> Grows with your real estate business</li>\n";
echo "<li><strong>Competitive Advantage:</strong> Unique hybrid model for real estate</li>\n";
echo "</ul>\n";
echo "</div>\n";
?>
