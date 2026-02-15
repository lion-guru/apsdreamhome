<?php
session_start();
include 'config.php';
require_role('Admin');
$partners = $conn->query("SELECT partner_email, SUM(points) as total_points FROM partner_rewards GROUP BY partner_email ORDER BY total_points DESC");
function getBadge($points) {
    if ($points >= 1000) return 'Platinum';
    if ($points >= 500) return 'Gold';
    if ($points >= 200) return 'Silver';
    return 'Bronze';
}
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Partner Badges & Tiers</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Partner Badges & Tiers</h2><table class='table table-bordered'><thead><tr><th>Partner</th><th>Total Points</th><th>Badge</th></tr></thead><tbody><?php while($p = $partners->fetch_assoc()): ?><tr><td><?= htmlspecialchars($p['partner_email']) ?></td><td><?= $p['total_points'] ?></td><td><?= getBadge($p['total_points']) ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Badges auto-assigned based on total points.</p></div></body></html>
