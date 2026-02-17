<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
$devices = $db->fetchAll("SELECT * FROM mobile_devices ORDER BY id DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Mobile App Integration</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Mobile App Integration</h2><form method='post'><div class='mb-3'><label>Device/User</label><input type='text' name='device_user' class='form-control'></div><div class='mb-3'><label>Push Token</label><input type='text' name='push_token' class='form-control'></div><div class='mb-3'><label>Platform</label><select name='platform' class='form-control'><option value='android'>Android</option><option value='ios'>iOS</option></select></div><button class='btn btn-success'>Register Device</button></form><table class='table table-bordered mt-4'><thead><tr><th>User/Device</th><th>Platform</th><th>Push Token</th><th>Registered</th></tr></thead><tbody><?php foreach($devices as $d): ?><tr><td><?= h($d['device_user']) ?></td><td><?= h($d['platform']) ?></td><td><?= h($d['push_token']) ?></td><td><?= $d['created_at'] ?></td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*Manage mobile app features, push notifications, and monitor usage here.</p></div></body></html>
