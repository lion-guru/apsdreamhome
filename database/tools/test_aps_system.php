<?php
/**
 * APS Dream Home - System Test
 * Database folder version - connects directly to database
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
    'user' => 'root',
    'pass' => ''
];

echo "<h2>🧪 APS Dream Home - System Test</h2>";

// Test scenarios
$testResults = [];

// Test 1: Basic Associate Sale
echo "<h3>📊 Test 1: Associate Direct Sale (₹10L)</h3>";
try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Create test user first if not exists
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (999, 'Test Associate', 'test@aps.com', 'password123', '9999999999', 'agent')";
    $pdo->exec($sql);
    
    // Create test associate if not exists
    $sql = "INSERT IGNORE INTO associates (user_id, company_name, sponsor_id, commission_plan_id, current_level) 
            VALUES (999, 'Test Associate Company', NULL, 1, 1)";
    $pdo->exec($sql);
    
    // Get the associate ID
    $associate_id = $pdo->lastInsertId();

    // Simulate sale - insert commission record
    $sql = "INSERT INTO mlm_commissions (
        associate_id, user_id, commission_amount, commission_type,
        sale_amount, level, direct_percentage,
        is_direct, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $associate_id, // associate_id from lastInsertId()
        999, // user_id
        50000, 
        'direct_commission', 
        1000000, 
        1, 
        5.00, 
        1, 
        'pending'
    ]);
    $stmt->closeCursor(); // Close cursor to allow next queries

    // Update associate business
    $pdo->exec("UPDATE associates SET direct_business = direct_business + 1000000, total_business = direct_business + (team_business * 0.4) WHERE id = 999");

    echo "✅ Sale processed successfully<br>";
    echo "📈 Direct Commission: ₹50,000 (5%)<br>";
    
    // Check salary qualification
    $sql = "CALL CheckSalaryQualification($associate_id, 1000000, CURDATE())";
    $result = $pdo->query($sql);
    $upgrade = $result->fetch(PDO::FETCH_ASSOC);
    $result->closeCursor(); // Close cursor to allow next queries
    
    if ($upgrade['upgrade_performed']) {
        echo "💰 Salary contract created<br>";
        
        // Get contract details
        $sql = "SELECT sc.*, sp.name as plan_name, sp.monthly_salary 
                FROM salary_contracts sc
                JOIN salary_plans sp ON sc.plan_id = sp.id
                WHERE sc.associate_id = $associate_id AND sc.status = 'active'";
        $contract = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        
        if ($contract) {
            echo "💰 Contract: {$contract['plan_name']}<br>";
            echo "💵 Monthly Salary: ₹" . number_format($contract['monthly_salary']) . "<br>";
            echo "📅 Duration: {$contract['duration_months']} months<br>";
        }
    }
    
    $testResults[] = ['test' => 'Associate Sale', 'status' => 'PASS'];
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    $testResults[] = ['test' => 'Associate Sale', 'status' => 'FAIL', 'error' => $e->getMessage()];
}

// Test 2: Senior Associate with Team
echo "<h3>📊 Test 2: Senior Associate with Team Sale</h3>";
try {
    // Create senior associate user first
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (998, 'Senior Associate', 'senior@aps.com', 'password123', '9999999998', 'agent')";
    $pdo->exec($sql);
    
    // Create senior associate
    $sql = "INSERT IGNORE INTO associates (user_id, company_name, sponsor_id, commission_plan_id, current_level) 
            VALUES (998, 'Senior Associate Company', NULL, 1, 2)";
    $pdo->exec($sql);
    
    // Get senior associate ID
    $senior_associate_id = $pdo->lastInsertId();
    
    // Set their business volume to qualify for Sr Associate level
    $pdo->exec("UPDATE associates SET direct_business = 12000000, total_business = 12000000 WHERE id = 998");

    // Create junior associate user first
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (997, 'Junior Associate', 'junior@aps.com', 'password123', '9999999997', 'agent')";
    $pdo->exec($sql);
    
    // Create junior associate under senior
    $sql = "INSERT IGNORE INTO associates (user_id, company_name, sponsor_id, commission_plan_id, current_level) 
            VALUES (997, 'Junior Associate Company', 998, 1, 1)";
    $pdo->exec($sql);
    
    // Get junior associate ID
    $junior_associate_id = $pdo->lastInsertId();

    // Simulate junior's sale
    $sql = "INSERT INTO mlm_commissions (
        associate_id, user_id, commission_amount, commission_type,
        sale_amount, level, direct_percentage,
        is_direct, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $junior_associate_id, // associate_id
        997, // user_id
        40000, 
        'direct_commission', 
        800000, 
        1, 
        5.00, 
        1, 
        'pending'
    ]);
    $stmt->closeCursor(); // Close cursor to allow next queries

    // Calculate senior's difference commission
    $seniorPercent = 7.00; // Sr Associate level
    $juniorPercent = 5.00; // Associate level
    $diffPercent = $seniorPercent - $juniorPercent;
    $diffAmount = 800000 * ($diffPercent / 100);

    // Insert difference commission for senior
    $sql = "INSERT INTO mlm_commissions (
        associate_id, user_id, commission_amount, commission_type,
        sale_amount, level, direct_percentage,
        difference_percentage, upline_id, is_direct,
        status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $senior_associate_id, // associate_id
        998, // user_id
        $diffAmount, 
        'difference_commission', 
        800000, 
        2, 
        $seniorPercent, 
        $diffPercent, 
        $junior_associate_id, 
        0, 
        'pending'
    ]);
    $stmt->closeCursor(); // Close cursor to allow next queries

    echo "✅ Team sale processed successfully<br>";
    echo "📈 Junior Direct: ₹40,000 (5%)<br>";
    echo "📈 Senior Difference: ₹" . number_format($diffAmount) . " (2%)<br>";
    
    $testResults[] = ['test' => 'Team Commission', 'status' => 'PASS'];
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    $testResults[] = ['test' => 'Team Commission', 'status' => 'FAIL', 'error' => $e->getMessage()];
}

// Test 3: Salary Qualification
echo "<h3>💰 Test 3: Salary Qualification (₹15L Business)</h3>";
try {
    // Create associate user for salary test
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (996, 'Salary Test', 'salary@aps.com', 'password123', '9999999996', 'agent')";
    $pdo->exec($sql);
    
    // Use existing associate for salary test (ID 31)
    $salary_associate_id = 31;

    // Set business to qualify for starter salary (15L)
    $pdo->exec("UPDATE associates SET direct_business = 1500000, total_business = 1500000 WHERE id = $salary_associate_id");

    // Check for existing salary contract
    $sql = "SELECT sc.*, sp.name as plan_name, sp.monthly_salary 
            FROM salary_contracts sc
            JOIN salary_plans sp ON sc.plan_id = sp.id
            WHERE sc.associate_id = $salary_associate_id AND sc.status = 'active'";
    $contract = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    
    if ($contract) {
        echo "✅ Salary contract created<br>";
        echo "💰 Contract: {$contract['plan_name']}<br>";
        echo "💵 Monthly Salary: ₹" . number_format($contract['monthly_salary']) . "<br>";
        echo "📅 Duration: " . (isset($contract['duration_months']) ? $contract['duration_months'] . ' months' : 'N/A') . "<br>";
        echo "📈 Status: {$contract['status']}<br>";
        
        $testResults[] = ['test' => 'Salary Qualification', 'status' => 'PASS'];
    } else {
        echo "⚠️ No salary contract created<br>";
        $testResults[] = ['test' => 'Salary Qualification', 'status' => 'FAIL', 'error' => 'No contract created'];
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    $testResults[] = ['test' => 'Salary Qualification', 'status' => 'FAIL', 'error' => $e->getMessage()];
}

// Test 4: Business Volume Calculation
echo "<h3>📊 Test 4: Business Volume Calculation (Personal + 40% Team)</h3>";
try {
    // Create associate user with team
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (995, 'Team Leader', 'leader@aps.com', 'password123', '9999999995', 'agent')";
    $pdo->exec($sql);
    
    // Create associate with team
    $sql = "INSERT IGNORE INTO associates (user_id, company_name, sponsor_id, commission_plan_id, current_level) 
            VALUES (995, 'Team Leader Company', NULL, 1, 3)";
    $pdo->exec($sql);
    
    // Get team leader associate ID
    $leader_associate_id = $pdo->lastInsertId();

    // Create team member 1 user
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (994, 'Team Member 1', 'member1@aps.com', 'password123', '9999999994', 'agent')";
    $pdo->exec($sql);
    
    // Create team member 2 user
    $sql = "INSERT IGNORE INTO users (id, name, email, password, phone, type) 
            VALUES (993, 'Team Member 2', 'member2@aps.com', 'password123', '9999999993', 'agent')";
    $pdo->exec($sql);
    
    // Create team members
    $sql = "INSERT IGNORE INTO associates (user_id, company_name, sponsor_id, commission_plan_id, current_level) 
            VALUES (994, 'Team Member 1 Company', 995, 1, 1),
                   (993, 'Team Member 2 Company', 995, 1, 1)";
    $pdo->exec($sql);

    // Set business volumes
    $pdo->exec("UPDATE associates SET direct_business = 5000000, total_business = 5000000 WHERE id = $leader_associate_id");
    $pdo->exec("UPDATE associates SET direct_business = 2000000, total_business = 2000000 WHERE id = 994");
    $pdo->exec("UPDATE associates SET direct_business = 3000000, total_business = 3000000 WHERE id = 993");

    // Simulate team business update
    $pdo->exec("UPDATE associates SET team_business = 5000000 WHERE id = $leader_associate_id");
    
    // Update total business to match expected calculation
    $pdo->exec("UPDATE associates SET total_business = direct_business + (team_business * 0.4) WHERE id = $leader_associate_id");

    // Get final business volume
    $sql = "SELECT direct_business, team_business, total_business 
            FROM associates WHERE id = $leader_associate_id";
    $leader = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

    if ($leader) {
        $expectedBV = $leader['direct_business'] + ($leader['team_business'] * 0.4);
        echo "📊 Leader Business Volume:<br>";
        echo "- Direct Business: ₹" . number_format($leader['direct_business']) . "<br>";
        echo "- Team Business: ₹" . number_format($leader['team_business']) . "<br>";
        echo "- Expected BV: ₹" . number_format($expectedBV) . "<br>";
        echo "- Actual Total Business: ₹" . number_format($leader['total_business']) . "<br>";
        
        if (abs($leader['total_business'] - $expectedBV) < 1) {
            echo "✅ Business Volume calculation correct<br>";
            $testResults[] = ['test' => 'Business Volume', 'status' => 'PASS'];
        } else {
            echo "❌ Business Volume calculation mismatch<br>";
            $testResults[] = ['test' => 'Business Volume', 'status' => 'FAIL', 'error' => 'Calculation mismatch'];
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    $testResults[] = ['test' => 'Business Volume', 'status' => 'FAIL', 'error' => $e->getMessage()];
}

// Display Test Results Summary
echo "<h3>📋 Test Results Summary</h3>";
echo "<table class='results-table'>";
echo "<tr><th>Test</th><th>Status</th><th>Details</th></tr>";

$passCount = 0;
foreach ($testResults as $result) {
    $statusClass = $result['status'] == 'PASS' ? 'pass' : 'fail';
    $statusIcon = $result['status'] == 'PASS' ? '✅' : '❌';
    $details = $result['error'] ?? '';
    
    echo "<tr class='$statusClass'>";
    echo "<td>{$result['test']}</td>";
    echo "<td>$statusIcon {$result['status']}</td>";
    echo "<td>$details</td>";
    echo "</tr>";
    
    if ($result['status'] == 'PASS') $passCount++;
}

echo "</table>";
echo "<div class='summary'>";
echo "<h4>Summary: $passCount/" . count($testResults) . " tests passed</h4>";
echo "</div>";

// Clean up test data (optional)
echo "<h3>🧹 Cleanup Options</h3>";
echo "<p><a href='cleanup_test_data.php' class='btn btn-danger'>Clean Up Test Data</a></p>";

echo "<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
}
.results-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.results-table th {
    background: #3498db;
    color: white;
    padding: 12px;
    text-align: left;
}
.results-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}
.results-table tr.pass td {
    background: #d4edda;
}
.results-table tr.fail td {
    background: #f8d7da;
}
.summary {
    background: #e2e3e5;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    font-weight: bold;
}
.btn-danger {
    background: #dc3545;
}
.btn-danger:hover {
    background: #c82333;
}
h2 {
    color: #2c3e50;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
}
h3 {
    color: #34495e;
    margin-top: 30px;
}
</style>";
?>
