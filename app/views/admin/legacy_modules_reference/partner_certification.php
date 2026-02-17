<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
$partners = $db->fetchAll("SELECT * FROM partner_certification ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Partner Certification & Monetization</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Partner Certification & Monetization</h2><form method='post'><div class='mb-3'><label>Partner Name</label><input type='text' name='partner_name' class='form-control'></div><div class='mb-3'><label>App/Integration</label><input type='text' name='app_name' class='form-control'></div><div class='mb-3'><label>Certification Status</label><select name='cert_status' class='form-control'><option value='pending'>Pending</option><option value='certified'>Certified</option><option value='rejected'>Rejected</option></select></div><div class='mb-3'><label>Revenue Share (%)</label><input type='number' name='revenue_share' class='form-control' min='0' max='100'></div><button class='btn btn-success'>Update Certification</button></form><table class='table table-bordered mt-4'><thead><tr><th>Partner</th><th>App</th><th>Status</th><th>Revenue Share</th><th>Created</th></tr></thead><tbody><?php foreach($partners as $p): ?><tr><td><?= h($p['partner_name']) ?></td><td><?= h($p['app_name']) ?></td><td><?= h($p['cert_status']) ?></td><td><?= h($p['revenue_share']) ?>%</td><td><?= $p['created_at'] ?></td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*Certify and monetize third-party apps/integrations. Manage partner revenue sharing and certification status.</p></div></body></html>
