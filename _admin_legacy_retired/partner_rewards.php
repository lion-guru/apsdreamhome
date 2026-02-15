<?php
session_start();
include 'config.php';
require_role('Admin');
$rewards = $conn->query("SELECT * FROM partner_rewards ORDER BY created_at DESC LIMIT 50");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Partner Rewards & Loyalty</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Partner Rewards & Loyalty Program</h2><form method='post'><div class='mb-3'><label>Partner Email</label><input type='email' name='partner_email' class='form-control'></div><div class='mb-3'><label>Reward Points</label><input type='number' name='points' class='form-control'></div><div class='mb-3'><label>Reason/Description</label><input type='text' name='description' class='form-control'></div><button class='btn btn-success'>Add Reward</button></form><table class='table table-bordered mt-4'><thead><tr><th>Partner</th><th>Points</th><th>Description</th><th>Date</th></tr></thead><tbody><?php while($r = $rewards->fetch_assoc()): ?><tr><td><?= htmlspecialchars($r['partner_email']) ?></td><td><?= htmlspecialchars($r['points']) ?></td><td><?= htmlspecialchars($r['description']) ?></td><td><?= $r['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Ready for partner badges, tiering, and automated rewards.</p></div></body></html>
