<?php
/**
 * Firebase â†” MySQL Sync Cron Script
 * 
 * Run via: php C:\xampp\htdocs\apsdreamhome\scripts\firebase_sync_cron.php
 * Or schedule in Windows Task Scheduler every 5-10 minutes.
 * 
 * Reads Firebase RTDB for new Block C bookings and syncs to MySQL.
 */

// Minimal bootstrap â€” just DB connection, no full framework
$configPath = __DIR__ . '/../app/Config/database.php';
$firebaseConfigPath = __DIR__ . '/../app/Config/firebase.php';

if (!file_exists($configPath)) {
    die("ERROR: database.php not found\n");
}

$dbConfig = require $configPath;
$firebaseConfig = file_exists($firebaseConfigPath) ? require $firebaseConfigPath : [];

// Connect to MySQL
try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[" . date('Y-m-d H:i:s') . "] Connected to MySQL\n";

    // Set tenant context for service compatibility
    if (class_exists('\App\Core\Middleware\TenantContext')) {
        try {
            \App\Core\Middleware\TenantContext::setById(1, $db);
        } catch (\Throwable $e) {
            // non-fatal
        }
    }
    $cronTenantId = 1;
    $cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
    $cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";
    $cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
} catch (PDOException $e) {
    die("ERROR: MySQL connection failed: " . $e->getMessage() . "\n");
}

// Firebase RTDB URL
$firebaseUrl = $firebaseConfig['client']['databaseURL'] ?? $firebaseConfig['databaseURL'] ?? '';
if (!$firebaseUrl) {
    echo "WARNING: No Firebase database URL configured. Skipping sync.\n";
    exit(0);
}

// Fetch bookings from Firebase RTDB
// Expected path: /blockC_bookings or /bookings
$endpoints = [
    $firebaseUrl . '/blockC_bookings.json',
    $firebaseUrl . '/bookings.json',
];

$allBookings = [];

foreach ($endpoints as $url) {
    echo "Fetching: $url\n";
    $response = @file_get_contents($url);
    if ($response === false || $response === 'null' || $response === '') {
        echo "  No data\n";
        continue;
    }
    $data = json_decode($response, true);
    if (is_array($data)) {
        $allBookings = array_merge($allBookings, $data);
        echo "  Found " . count($data) . " bookings\n";
    }
}

if (empty($allBookings)) {
    echo "No bookings to sync.\n";
    exit(0);
}

echo "Total Firebase bookings to process: " . count($allBookings) . "\n\n";

$synced = 0;
$skipped = 0;
$errors = 0;

foreach ($allBookings as $key => $booking) {
    $plotId = $booking['plotId'] ?? $booking['plot_id'] ?? '';
    $name = $booking['name'] ?? $booking['customerName'] ?? '';
    $phone = $booking['phone'] ?? $booking['customerPhone'] ?? '';
    $timestamp = $booking['timestamp'] ?? $booking['created_at'] ?? date('Y-m-d H:i:s');

    if (!$plotId || !$name || !$phone) {
        echo "SKIP [$key]: Missing required fields (plotId=$plotId, name=$name)\n";
        $skipped++;
        continue;
    }

    try {
        // Find plot in MySQL
        $stmt = $db->prepare("
            SELECT p.id, p.colony_id, p.total_price, p.plot_number
            FROM plots p
            WHERE p.plot_number = ? AND p.block = 'C' AND p.is_active = 1{$cronTenantSql}
        ");
        $stmt->execute([$plotId]);
        $plot = $stmt->fetch();

        if (!$plot) {
            echo "SKIP [$key]: Plot '$plotId' not found in MySQL\n";
            $skipped++;
            continue;
        }

        // Check if booking already exists
        $existing = $db->prepare("
            SELECT id FROM plot_bookings WHERE plot_id = ? AND status NOT IN ('cancelled'){$cronTenantSql}
        ");
        $existing->execute([$plot['id']]);
        if ($existing->fetch()) {
            echo "SKIP [$key]: Booking already exists for plot '$plotId'\n";
            $skipped++;
            continue;
        }

        // Find or create customer user
        $user = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
        $user->execute([$phone]);
        $user = $user->fetch();

        if (!$user) {
            $stmt = $db->prepare("INSERT INTO users (name, phone, role, created_at{$cronTenantCol}) VALUES (?, ?, 'customer', NOW(){$cronTenantVal})");
            $stmt->execute([$name, $phone]);
            $customerId = $db->lastInsertId();
        } else {
            $customerId = $user['id'];
        }

        // Create booking
        $bookingNumber = 'BK' . date('Ymd') . strtoupper(substr(md5($plotId . $phone), 0, 6));
        $stmt = $db->prepare("
            INSERT INTO plot_bookings (plot_id, customer_id, booking_number, booking_date, total_plot_value, booking_amount, status, approval_status, channel, notes, created_at{$cronTenantCol})
            VALUES (?, ?, ?, ?, ?, ?, 'token_paid', 'pending', 'firebase_sync', ?, NOW(){$cronTenantVal})
        ");
        $stmt->execute([
            $plot['id'], $customerId, $bookingNumber,
            date('Y-m-d', strtotime($timestamp)),
            $plot['total_price'] ?? 0,
            $plot['total_price'] ?? 0,
            json_encode(['source' => 'firebase_cron_sync', 'firebase_key' => $key, 'firebase_timestamp' => $timestamp])
        ]);

        // Update plot status
        $db->prepare("UPDATE plots SET status = 'booked', customer_id = ?, booking_date = ? WHERE id = ?{$cronTenantSql}")
            ->execute([$customerId, date('Y-m-d', strtotime($timestamp)), $plot['id']]);

        echo "OK [$key]: Synced plot '$plotId' â†’ booking '$bookingNumber'\n";
        $synced++;

    } catch (Exception $e) {
        echo "ERROR [$key]: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n--- Sync Complete ---\n";
echo "Synced: $synced | Skipped: $skipped | Errors: $errors\n";?>