<?php
session_start();
include 'config.php';
require_role('Admin');
$apps = $conn->query("SELECT * FROM marketplace_apps ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Marketplace & Ecosystem</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Marketplace & Ecosystem</h2><form method='post'><div class='mb-3'><label>App/Integration Name</label><input type='text' name='app_name' class='form-control'></div><div class='mb-3'><label>Provider/Developer</label><input type='text' name='provider' class='form-control'></div><div class='mb-3'><label>App URL</label><input type='text' name='app_url' class='form-control'></div><button class='btn btn-success'>Add App</button></form><table class='table table-bordered mt-4'><thead><tr><th>Name</th><th>Provider</th><th>URL</th><th>Created</th></tr></thead><tbody><?php while($a = $apps->fetch_assoc()): ?><tr><td><?= htmlspecialchars($a['app_name']) ?></td><td><?= htmlspecialchars($a['provider']) ?></td><td><?= htmlspecialchars($a['app_url']) ?></td><td><?= $a['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Allow third-party developers/partners to build and offer integrations, apps, or services on your platform.</p></div></body></html>
