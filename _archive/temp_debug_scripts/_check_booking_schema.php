<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== plot_bookings ===" . PHP_EOL;
$r = $pdo->query('SHOW CREATE TABLE plot_bookings')->fetch();
echo $r[1] . PHP_EOL . PHP_EOL;

echo "=== booking_payment_schedules ===" . PHP_EOL;
$r = $pdo->query('SHOW CREATE TABLE booking_payment_schedules')->fetch();
echo $r[1] . PHP_EOL . PHP_EOL;

echo "=== plot_bookings data ===" . PHP_EOL;
$rows = $pdo->query('SELECT id, plot_id, customer_id, associate_id, booking_number, status, total_plot_value, booking_amount, booking_date FROM plot_bookings ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['id']}: plot={$r['plot_id']} cust={$r['customer_id']} assoc={$r['associate_id']} [{$r['status']}] â‚¹{$r['total_plot_value']}" . PHP_EOL;
if (empty($rows)) echo "  (empty)" . PHP_EOL;

echo PHP_EOL . "=== existing agents/users ===" . PHP_EOL;
$rows = $pdo->query("SELECT u.id, u.name, u.email, u.role, a.id as assoc_id FROM users u LEFT JOIN associates a ON a.user_id = u.id WHERE u.role IN ('agent','associate','admin') ORDER BY u.id LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  user={$r['id']}: {$r['name']} ({$r['role']}) assoc_id={$r['assoc_id']}" . PHP_EOL;

echo PHP_EOL . "=== motiram plots sample ===" . PHP_EOL;
$rows = $pdo->query("SELECT id, plot_number, block, price_per_sqft, total_price, status FROM plots WHERE colony_id = 7 ORDER BY plot_number LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['id']}: {$r['plot_number']} ({$r['block']}) â‚¹{$r['price_per_sqft']}/sqft = â‚¹{$r['total_price']} [{$r['status']}]" . PHP_EOL;?>