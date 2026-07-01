<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== PLOT_BOOKINGS COLUMNS ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM plot_bookings');
while ($r = $stmt->fetch()) echo "  {$r['Field']} {$r['Type']}\n";

echo "\n=== BOOKINGS ===\n";
$stmt = $pdo->query('SELECT id, plot_id, customer_id, associate_id, status, agreement_value FROM plot_bookings ORDER BY id');
while ($r = $stmt->fetch()) echo "  id={$r['id']} plot={$r['plot_id']} customer={$r['customer_id']} assoc={$r['associate_id']} status={$r['status']} value={$r['agreement_value']}\n";

echo "\n=== LEDGER SUMMARY ===\n";
$stmt = $pdo->query('SELECT commission_type, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY commission_type ORDER BY total DESC');
while ($r = $stmt->fetch()) echo "  {$r['commission_type']}: {$r['cnt']} entries, total={$r['total']}\n";
