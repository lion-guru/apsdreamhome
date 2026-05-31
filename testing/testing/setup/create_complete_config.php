<?php
/**
 * Create Complete MLM/Payout Configuration in Database
 */

$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== CREATING COMPLETE MLM/PAYOUT CONFIGURATION ===\n\n";

// 1. Add Payout Configuration to app_config
echo "🔧 ADDING PAYOUT CONFIGURATION:\n";

$payoutConfigs = [
    // Payout Thresholds
    'payout_minimum_threshold' => '500',
    'payout_processing_fee_below_1000' => '10',
    'payout_processing_fee_above_1000' => '0',
    'payout_tax_deduction_rate' => '5', // 5% TDS
    'payout_tax_deduction_high_rate' => '10', // 10% for high amounts
    'payout_tax_threshold' => '10000', // Above 10k apply 10% TDS
    
    // Payout Frequency
    'payout_frequency' => 'monthly',
    'payout_processing_day' => '25', // 25th of each month
    'payout_processing_time' => '48', // 48 hours
    
    // Payment Methods
    'payment_methods_available' => 'bank_transfer,upi,cheque,cash',
    'payment_method_bank_transfer' => 'active',
    'payment_method_upi' => 'active',
    'payment_method_cheque' => 'active',
    'payment_method_cash' => 'inactive',
    
    // Payout Limits
    'payout_daily_limit' => '50000',
    'payout_monthly_limit' => '500000',
    'payout_emergency_limit' => '100000',
    
    // Commission Settings
    'commission_calculation_method' => 'percentage',
    'commission_payment_delay' => '7', // 7 days after approval
    'commission_minimum_approval' => '100',
    
    // MLM Settings
    'mlm_maximum_levels' => '10',
    'mlm_commission_levels' => '5',
    'mlm_direct_commission_base' => '10', // 10% base rate
    'mlm_team_commission_base' => '2', // 2% base rate
    'mlm_level_difference_enabled' => 'true',
    'mlm_matching_bonus_enabled' => 'true',
    'mlm_leadership_bonus_enabled' => 'true',
    
    // Bonus Settings
    'welcome_bonus_amount' => '50',
    'welcome_bonus_minimum_sales' => '100',
    'welcome_bonus_timeframe' => '30',
    'fast_start_bonus_amount' => '100',
    'fast_start_minimum_sales' => '500',
    'fast_start_timeframe' => '60',
    'leadership_pool_percentage' => '1',
    'leadership_minimum_rank' => 'Diamond',
    'leadership_minimum_team_sales' => '10000'
];

foreach ($payoutConfigs as $key => $value) {
    // Check if already exists
    $checkResult = $mysqli->query("SELECT COUNT(*) as count FROM app_config WHERE config_key = '$key'");
    $exists = $checkResult->fetch_assoc()['count'];
    
    if ($exists == 0) {
        $stmt = $mysqli->prepare("INSERT INTO app_config (config_key, config_value, config_type, description) VALUES (?, ?, 'string', ?)");
        $description = "Payout/MLM Configuration: " . str_replace('_', ' ', $key);
        $stmt->bind_param('sss', $key, $value, $description);
        $stmt->execute();
        echo "  ✅ Created: $key = $value\n";
    } else {
        echo "  ℹ️ Exists: $key\n";
    }
}

// 2. Create JSON configuration files
echo "\n🔧 CREATING JSON CONFIGURATION FILES:\n";

// MLM Configuration JSON
$mlmConfig = [
    'levels' => [
        1 => ['name' => 'Associate', 'direct' => 10, 'team' => 2, 'joining_fee' => 100],
        2 => ['name' => 'Bronze', 'direct' => 12, 'team' => 3, 'joining_fee' => 150],
        3 => ['name' => 'Silver', 'direct' => 14, 'team' => 4, 'joining_fee' => 200],
        4 => ['name' => 'Gold', 'direct' => 16, 'team' => 5, 'joining_fee' => 250],
        5 => ['name' => 'Platinum', 'direct' => 18, 'team' => 6, 'joining_fee' => 300],
        6 => ['name' => 'Diamond', 'direct' => 20, 'team' => 7, 'joining_fee' => 350],
        7 => ['name' => 'Crown Diamond', 'direct' => 22, 'team' => 8, 'joining_fee' => 400],
        8 => ['name' => 'Executive', 'direct' => 24, 'team' => 9, 'joining_fee' => 450],
        9 => ['name' => 'Presidential', 'direct' => 26, 'team' => 10, 'joining_fee' => 500],
        10 => ['name' => 'Ambassador', 'direct' => 30, 'team' => 12, 'joining_fee' => 1000]
    ],
    'commission' => [
        'calculation_method' => 'percentage',
        'payment_delay_days' => 7,
        'minimum_approval_amount' => 100
    ],
    'bonuses' => [
        'welcome' => ['amount' => 50, 'min_sales' => 100, 'days' => 30],
        'fast_start' => ['amount' => 100, 'min_sales' => 500, 'days' => 60],
        'leadership_pool' => ['percentage' => 1, 'min_rank' => 'Diamond', 'min_team_sales' => 10000]
    ]
];

file_put_contents(__DIR__ . '/config/mlm_config.json', json_encode($mlmConfig, JSON_PRETTY_PRINT));
echo "  ✅ Created: config/mlm_config.json\n";

// Payout Configuration JSON
$payoutConfig = [
    'thresholds' => [
        'minimum_payout' => 500,
        'processing_fee_below_1000' => 10,
        'processing_fee_above_1000' => 0,
        'tax_deduction_rate' => 5,
        'tax_deduction_high_rate' => 10,
        'tax_threshold' => 10000
    ],
    'frequency' => [
        'type' => 'monthly',
        'processing_day' => 25,
        'processing_hours' => 48
    ],
    'limits' => [
        'daily_limit' => 50000,
        'monthly_limit' => 500000,
        'emergency_limit' => 100000
    ],
    'payment_methods' => [
        'bank_transfer' => ['active' => true, 'fee' => 0],
        'upi' => ['active' => true, 'fee' => 0, 'max_amount' => 50000],
        'cheque' => ['active' => true, 'fee' => 0, 'processing_days' => 3],
        'cash' => ['active' => false, 'fee' => 0]
    ]
];

file_put_contents(__DIR__ . '/config/payout_config.json', json_encode($payoutConfig, JSON_PRETTY_PRINT));
echo "  ✅ Created: config/payout_config.json\n";

// 3. Update .env file
echo "\n🔧 UPDATING .env FILE:\n";
$envFile = __DIR__ . '/.env';
$envContent = '';

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    // Remove existing MLM/Payout configs
    $lines = explode("\n", $envContent);
    $filteredLines = [];
    foreach ($lines as $line) {
        if (strpos($line, 'PAYOUT_') === 0 || strpos($line, 'MLM_') === 0 || strpos($line, 'COMMISSION_') === 0) {
            continue;
        }
        $filteredLines[] = $line;
    }
    $envContent = implode("\n", $filteredLines);
}

// Add new configurations
$envContent .= "\n# MLM Configuration - Auto-generated\n";
$envContent .= "MLM_MAXIMUM_LEVELS=10\n";
$envContent .= "MLM_COMMISSION_LEVELS=5\n";
$envContent .= "MLM_DIRECT_COMMISSION_BASE=10\n";
$envContent .= "MLM_TEAM_COMMISSION_BASE=2\n";

$envContent .= "\n# Payout Configuration - Auto-generated\n";
$envContent .= "PAYOUT_MINIMUM_THRESHOLD=500\n";
$envContent .= "PAYOUT_PROCESSING_FEE_BELOW_1000=10\n";
$envContent .= "PAYOUT_TAX_DEDUCTION_RATE=5\n";
$envContent .= "PAYOUT_FREQUENCY=monthly\n";
$envContent .= "PAYOUT_PROCESSING_DAY=25\n";

file_put_contents($envFile, $envContent);
echo "  ✅ Updated: .env\n";

// 4. Create PHP configuration files
echo "\n🔧 CREATING PHP CONFIGURATION FILES:\n";

// MLM Settings PHP
$mlmSettingsPhp = "<?php\n\n// MLM Configuration - Auto-generated from database\nreturn [\n";
foreach ($payoutConfigs as $key => $value) {
    if (strpos($key, 'mlm_') === 0) {
        $phpKey = strtoupper($key);
        $mlmSettingsPhp .= "    '$phpKey' => '$value',\n";
    }
}
$mlmSettingsPhp .= "];\n";

file_put_contents(__DIR__ . '/config/mlm_settings.php', $mlmSettingsPhp);
echo "  ✅ Created: config/mlm_settings.php\n";

// Payout Settings PHP
$payoutSettingsPhp = "<?php\n\n// Payout Configuration - Auto-generated from database\nreturn [\n";
foreach ($payoutConfigs as $key => $value) {
    if (strpos($key, 'payout_') === 0) {
        $phpKey = strtoupper($key);
        $payoutSettingsPhp .= "    '$phpKey' => '$value',\n";
    }
}
$payoutSettingsPhp .= "];\n";

file_put_contents(__DIR__ . '/config/payout_settings.php', $payoutSettingsPhp);
echo "  ✅ Created: config/payout_settings.php\n";

// 5. Show current configuration summary
echo "\n📊 CONFIGURATION SUMMARY:\n";

echo "\n🗄️ DATABASE CONFIGURATION:\n";
$result = $mysqli->query("SELECT config_key, config_value FROM app_config WHERE config_key LIKE '%payout%' OR config_key LIKE '%mlm%' ORDER BY config_key");
while ($row = $result->fetch_assoc()) {
    echo "  📋 {$row['config_key']}: {$row['config_value']}\n";
}

echo "\n📄 FILES CREATED:\n";
echo "  ✅ config/mlm_config.json\n";
echo "  ✅ config/payout_config.json\n";
echo "  ✅ config/mlm_settings.php\n";
echo "  ✅ config/payout_settings.php\n";
echo "  ✅ .env (updated)\n";

echo "\n🎯 CONFIGURATION LOCATIONS:\n";
echo "  🗄️ PRIMARY: app_config table (database)\n";
echo "  📄 JSON: config/ directory\n";
echo "  📄 PHP: config/ directory\n";
echo "  🌍 ENV: .env file\n";
echo "  💻 SERVICES: app/Services/ classes\n";

echo "\n🏁 COMPLETE CONFIGURATION SYSTEM READY\n";

?>
