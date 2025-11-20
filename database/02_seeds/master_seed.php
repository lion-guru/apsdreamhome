<?php
/**
 * Master Data Seeder
 * Run: php database/02_seeds/master_seed.php
 */

$DB_HOST = 'localhost';
$DB_NAME = 'apsdreamhome';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    die("DB connection failed: " . $e->getMessage());
}

/* ---------- disable FK checks ---------- */
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

/* ---------- Helper: random date ---------- */
function rand_date($start = '-90 days', $end = 'now') {
    return date('Y-m-d H:i:s', rand(strtotime($start), strtotime($end)));
}

/* ---------- 1. USERS (agents + customers) ---------- */
$agents = [
    ['name' => 'Aarav Sharma',    'email' => 'aarav@aps.com', 'phone' => '9000011111', 'type' => 'agent'],
    ['name' => 'Diya Verma',      'email' => 'diya@aps.com', 'phone' => '9000022222', 'type' => 'agent'],
    ['name' => 'Kabir Singh',     'email' => 'kabir@aps.com', 'phone' => '9000033333', 'type' => 'agent'],
    ['name' => 'Ananya Rao',      'email' => 'ananya@aps.com', 'phone' => '9000044444', 'type' => 'agent'],
    ['name' => 'Vivaan Gupta',    'email' => 'vivaan@aps.com', 'phone' => '9000055555', 'type' => 'agent']
];
$customers = [];
for ($i = 1; $i <= 30; $i++) {
    $customers[] = [
        'name'  => 'Customer ' . $i,
        'email' => 'cust' . $i . '@mail.com',
        'phone' => '91' . sprintf('%08d', rand(10000000, 99999999)),
        'type'  => 'customer'
    ];
}
$all_users = array_merge($agents, $customers);
$user_ids = [];
$pdo->exec("TRUNCATE users");
$stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, type, created_at) VALUES (?,?,?,?,?,?)");
foreach ($all_users as $u) {
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $stmt->execute([$u['name'], $u['email'], $u['phone'], $hash, $u['type'], rand_date()]);
    $user_ids[] = $pdo->lastInsertId();
}

/* ---------- 2. PROPERTY TYPES ---------- */
$types = ['apartment', 'villa', 'plot', 'commercial'];
$pdo->exec("TRUNCATE property_types");
$type_stmt = $pdo->prepare("INSERT INTO property_types (type, description, created_at, updated_at) VALUES (?,?, NOW(), NOW())");
foreach ($types as $t) {
    $type_stmt->execute([ucfirst($t), ucfirst($t) . ' properties']);
}

/* ---------- 3. PROPERTIES (50) ---------- */
$titles = [
    '2 BHK Luxurious Flat', '3 BHK Premium Apartment', '4 BHK Penthouse', '5 BHK Villa with Pool',
    'Residential Plot 1500 sqft', 'Commercial Shop Prime Location', 'Studio Apartment', 'Independent House',
    'Duplex Villa', 'Agricultural Land', 'Gated Community Plot', 'High-Street Retail Space'
];
$pdo->exec("TRUNCATE properties");
$prop_stmt = $pdo->prepare("INSERT INTO properties (
  title, description, type, price, location, status, created_by, created_at, updated_at
) VALUES (?,?,?,?,?,?,?,?,?)");
for ($i = 1; $i <= 50; $i++) {
    $type_options = ['apartment', 'house', 'land', 'commercial'];
    $type = $type_options[rand(0, 3)];
    $agent_id = $user_ids[array_rand(array_slice($user_ids, 0, 5))]; // agents only
    $price = rand(20, 200) * 100000;
    $title = $titles[array_rand($titles)] . ' #' . $i;
    $desc = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. This property offers excellent value and modern amenities.";
    $city = ['Gorakhpur', 'Lucknow', 'Varanasi', 'Kanpur', 'Allahabad'][rand(0, 4)];
    $location = "Sector-" . rand(1, 20) . ", " . $city . ", UP";
    $status = ['available', 'sold', 'booked'][rand(0, 2)];
    $prop_stmt->execute([
        $title, $desc, $type, $price, $location, $status, $agent_id, rand_date(), rand_date()
    ]);
}

/* ---------- 4. LEADS (80) ---------- */
$lead_sources = ['website', 'facebook', 'google', 'referral', 'walk-in'];
$statuses     = ['new', 'contacted', 'qualified', 'proposal', 'closed_won', 'closed_lost'];
$pdo->exec("TRUNCATE leads");
$lead_stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, assigned_to, property_interest, notes, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?)");
for ($i = 1; $i <= 80; $i++) {
    $name = 'Lead ' . $i;
    $mail = 'lead' . $i . '@example.com';
    $phone = '91' . sprintf('%08d', rand(10000000, 99999999));
    $src = $lead_sources[array_rand($lead_sources)];
    $stat = $statuses[array_rand($statuses)];
    $agent_id = $user_ids[array_rand(array_slice($user_ids, 0, 5))];
    $property_interest = ['apartment', 'villa', 'plot'][rand(0, 2)];
    $notes = "Interested in " . $property_interest;
    $lead_stmt->execute([$name, $mail, $phone, $src, $stat, $agent_id, $property_interest, $notes, rand_date(), rand_date()]);
}

/* ---------- 5. BOOKINGS (60) ---------- */
$prop_ids = $pdo->query("SELECT id FROM properties WHERE status='available' ORDER BY RAND() LIMIT 60")->fetchAll(PDO::FETCH_COLUMN);
$cust_ids = array_filter($user_ids, fn($id) => $id > 5); // customers only
$pdo->exec("TRUNCATE bookings");
$book_stmt = $pdo->prepare("INSERT INTO bookings (property_id, customer_id, booking_date, amount, status, created_at) VALUES (?,?,?,?,?,?)");
foreach ($prop_ids as $pid) {
    $cid = $cust_ids[array_rand($cust_ids)];
    $amt = rand(50000, 200000);
    $bdate = rand_date('-60 days', '+30 days');
    $stat = ['confirmed', 'cancelled', 'completed'][rand(0, 2)];
    $book_stmt->execute([$pid, $cid, $bdate, $amt, $stat, rand_date()]);
}

/* ---------- 6. PAYMENTS (100) ---------- */
$book_ids = $pdo->query("SELECT id FROM bookings")->fetchAll(PDO::FETCH_COLUMN);
$cust_ids = array_filter($user_ids, fn($id) => $id > 5); // customers only
$pdo->exec("TRUNCATE payments");
$pay_stmt = $pdo->prepare("INSERT INTO payments (booking_id, customer_id, amount, payment_type, payment_method, transaction_id, payment_date, status, notes, created_by, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
for ($i = 0; $i < 100; $i++) {
    $bid = $book_ids[array_rand($book_ids)];
    $cid = $cust_ids[array_rand($cust_ids)];
    $amt = rand(50000, 500000);
    $pay_type = ['booking', 'installment', 'final'][rand(0, 2)];
    $method = ['cash', 'cheque', 'neft', 'upi'][rand(0, 3)];
    $tid = 'TXN' . strtoupper(bin2hex(random_bytes(6)));
    $pay_date = date('Y-m-d', rand(strtotime('-90 days'), strtotime('now')));
    $stat = ['pending', 'completed', 'failed'][rand(0, 2)];
    $notes = "Payment for booking ID: " . $bid;
    $agent_id = $user_ids[array_rand(array_slice($user_ids, 0, 5))]; // agent who processed payment
    $pay_stmt->execute([$bid, $cid, $amt, $pay_type, $method, $tid, $pay_date, $stat, $notes, $agent_id, rand_date(), rand_date()]);
}

/* ---------- 7. ASSOCIATES ---------- */
$pdo->exec("TRUNCATE associates");
$assoc_stmt = $pdo->prepare("INSERT INTO associates (user_id, company_name, registration_number, commission_rate, created_at, updated_at) VALUES (?,?,?,?,?,?)");
// Create 5 associate records for agents
for ($i = 0; $i < 5; $i++) {
    $uid = $user_ids[$i]; // Use first 5 users (agents)
    $company_name = "APS Associates " . ($i + 1);
    $reg_number = "REG" . sprintf('%06d', rand(100000, 999999));
    $commission_rate = rand(2, 8) + (rand(0, 99) / 100); // 2.00 to 8.99%
    $assoc_stmt->execute([$uid, $company_name, $reg_number, $commission_rate, rand_date(), rand_date()]);
}

echo "✅ Master seed complete!\n";
echo "Users: " . count($all_users) . " | Properties: 50 | Leads: 80 | Bookings: " . count($prop_ids) . " | Payments: 100 | Associates: 5\n";

/* ---------- re-enable FK checks ---------- */
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "Default agent login: aarav@aps.com / 123456\n";
echo "Default customer login: cust1@mail.com / 123456\n";
