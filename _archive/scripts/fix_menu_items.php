<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Fix menu items that point to wrong portals
$fixes = [
    // [244] Associate Dashboard -> /associate/dashboard -> should be /admin/mlm/associates
    [244, '/admin/mlm/associates'],
    // [275] Customer Referrals -> /user/referral -> should be /admin/users or similar
    [275, '/admin/customers'],
];

foreach ($fixes as $fix) {
    $id = $fix[0];
    $url = $fix[1];
    $stmt = $pdo->prepare("UPDATE admin_menu_items SET url = ? WHERE id = ?");
    $stmt->execute([$url, $id]);
    echo "Fixed ID $id -> $url\n";
}

echo "Done!\n";?>