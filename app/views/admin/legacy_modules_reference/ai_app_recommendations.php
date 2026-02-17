<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
$apps = $db->fetchAll("SELECT * FROM app_store ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI-Driven App Recommendations</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI-Driven App Recommendations</h2><form method='post'><div class='mb-3'><label>User/Client Preferences</label><input type='text' name='preferences' class='form-control' placeholder='e.g., CRM, analytics, payments'></div><button class='btn btn-success'>Get Recommendations</button></form><table class='table table-bordered mt-4'><thead><tr><th>App Name</th><th>Provider</th><th>Price</th><th>URL</th><th>Created</th></tr></thead><tbody><?php foreach($apps as $a): ?><tr><td><?= h($a['app_name']) ?></td><td><?= h($a['provider']) ?></td><td>₹<?= h($a['price']) ?></td><td><?= h($a['app_url']) ?></td><td><?= $a['created_at'] ?></td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*AI integration ready. Connect to NLP/ML model for personalized app recommendations based on user/client preferences.</p></div></body></html>
