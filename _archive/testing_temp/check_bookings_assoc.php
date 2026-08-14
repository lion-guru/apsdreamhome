<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Find bookings with valid associate_id
$stmt = $pdo->query('SELECT id, sales_manager_id, associate_id, customer_id, agreement_value FROM plot_bookings WHERE associate_id IS NOT NULL AND associate_id != 0');
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($bookings as $b) {
    $stmt2 = $pdo->prepare('SELECT user_id FROM associates WHERE user_id = ?');
    $stmt2->execute([$b['associate_id']]);
    $assoc = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "Booking $b[id]: associate_id=$b[associate_id], has_assoc=" . ($assoc ? 'YES' : 'NO') . ", customer=$b[customer_id]\n";
}?>