<?php
/**
 * APS Dream Home - Verify User Preferences
 * 
 * This script verifies that the user preferences table and data are correctly set up.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

echo "<h1>User Preferences Verification</h1>";
echo "<pre>";

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to database\n\n";

// Check if user_preferences table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'user_preferences'");

if ($tableExists->num_rows === 0) {
    die("❌ user_preferences table does not exist. Please run migrations first.\n");
}

echo "✅ user_preferences table exists\n";

// Check table structure
$expectedColumns = [
    'id' => 'int',
    'user_id' => 'int',
    'preference_key' => 'varchar',
    'preference_value' => 'text',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp'
];

$structureValid = true;
$result = $conn->query("DESCRIBE user_preferences");
$actualColumns = [];

while ($row = $result->fetch_assoc()) {
    $type = strtolower($row['Type']);
    $actualColumns[$row['Field']] = strpos($type, 'int') !== false ? 'int' : 
                                   (strpos($type, 'varchar') !== false ? 'varchar' : 
                                   (strpos($type, 'text') !== false ? 'text' : 
                                   (strpos($type, 'timestamp') !== false ? 'timestamp' : $type)));
}

foreach ($expectedColumns as $column => $type) {
    if (!isset($actualColumns[$column])) {
        echo "❌ Missing column: $column\n";
        $structureValid = false;
    } elseif ($actualColumns[$column] !== $type) {
        echo "⚠️  Type mismatch for column $column: expected $type, found " . $actualColumns[$column] . "\n";
        $structureValid = false;
    }
}

if ($structureValid) {
    echo "✅ Table structure is valid\n";
}

// Check for indexes
$indexes = [
    'PRIMARY' => ['id'],
    'unique_user_preference' => ['user_id', 'preference_key'],
    'idx_user_preferences_key' => ['preference_key']
];

$result = $conn->query("SHOW INDEX FROM user_preferences");
$actualIndexes = [];

while ($row = $result->fetch_assoc()) {
    $indexName = $row['Key_name'];
    if (!isset($actualIndexes[$indexName])) {
        $actualIndexes[$indexName] = [];
    }
    $actualIndexes[$indexName][] = $row['Column_name'];
}

$indexesValid = true;
foreach ($indexes as $indexName => $columns) {
    if (!isset($actualIndexes[$indexName])) {
        echo "❌ Missing index: $indexName\n";
        $indexesValid = false;
    } else {
        if ($actualIndexes[$indexName] !== $columns) {
            echo "⚠️  Index $indexName has different columns: expected " . 
                 implode(',', $columns) . 
                 ", found " . implode(',', $actualIndexes[$indexName]) . "\n";
            $indexesValid = false;
        }
    }
}

if ($indexesValid) {
    echo "✅ Indexes are valid\n";
}

// Check data
$result = $conn->query("SELECT COUNT(*) as total FROM user_preferences");
$row = $result->fetch_assoc();
$totalPreferences = $row['total'];

$result = $conn->query("SELECT COUNT(DISTINCT user_id) as users FROM user_preferences");
$row = $result->fetch_assoc();
$usersWithPreferences = $row['users'];

// Get total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$row = $result->fetch_assoc();
$totalUsers = $row['total'];

echo "\n📊 User Preferences Statistics:\n";
echo "- Total preferences: $totalPreferences\n";
echo "- Users with preferences: $usersWithPreferences\n";
echo "- Total users: $totalUsers\n";

if ($usersWithPreferences < $totalUsers) {
    $missing = $totalUsers - $usersWithPreferences;
    echo "⚠️  $missing users are missing preferences\n";
    
    // Check if we should fix this
    echo "\nWould you like to fix missing user preferences? (y/n): ";
    $handle = fopen('php://stdin', 'r');
    $input = trim(fgets($handle));
    
    if (strtolower($input) === 'y') {
        echo "\nFixing missing user preferences...\n";
        
        // Get users without preferences
        $result = $conn->query("
            SELECT u.id 
            FROM users u
            LEFT JOIN user_preferences up ON u.id = up.user_id
            WHERE up.id IS NULL
        ");
        
        $fixed = 0;
        while ($user = $result->fetch_assoc()) {
            $userId = $user['id'];
            
            // Insert default dashboard layout for admins and agents
            $conn->query("
                INSERT INTO user_preferences (user_id, preference_key, preference_value)
                VALUES (
                    $userId, 
                    'dashboard_layout',
                    '{\"widgets\":[\"recent_properties\",\"leads\",\"visits\",\"revenue\"]}'
                )
            ");
            
            // Insert default notification preferences
            $conn->query("
                INSERT INTO user_preferences (user_id, preference_key, preference_value)
                VALUES (
                    $userId, 
                    'notification_preferences',
                    '{\"email\":true,\"in_app\":true,\"sms\":false}'
                )
            ");
            
            $fixed++;
        }
        
        echo "✅ Added default preferences for $fixed users\n";
    }
} else {
    echo "✅ All users have preferences set\n";
}

// Check for invalid preference values
$result = $conn->query("
    SELECT id, user_id, preference_key, preference_value
    FROM user_preferences
    WHERE preference_value NOT LIKE '{%' 
    AND preference_value NOT LIKE '[%'
    AND preference_value NOT IN ('true', 'false', 'null')
    AND preference_value NOT REGEXP '^[0-9]+(\\.[0-9]+)?$'
    LIMIT 10
");

$invalidPreferences = [];
while ($row = $result->fetch_assoc()) {
    $invalidPreferences[] = $row;
}

if (!empty($invalidPreferences)) {
    echo "\n⚠️  Found " . count($invalidPreferences) . " potentially invalid preference values\n";
    echo "Sample of invalid values:\n";
    foreach (array_slice($invalidPreferences, 0, 5) as $pref) {
        echo "- User ID: {$pref['user_id']}, Key: {$pref['preference_key']}, Value: " . 
             substr($pref['preference_value'], 0, 50) . (strlen($pref['preference_value']) > 50 ? '...' : '') . "\n";
    }
} else {
    echo "\n✅ All preference values appear to be valid JSON or simple values\n";
}

// Close connection
$conn->close();

echo "\n=== Verification complete ===\n";
?>

<style>
    pre {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.5;
    }
    .success { color: #28a745; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
</style>
