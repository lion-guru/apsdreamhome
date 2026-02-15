<?php
session_start();
include 'config.php';
require_role('Admin');
$instances = $conn->query("SELECT * FROM saas_instances ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>White-Label SaaS Management</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>White-Label SaaS Platform</h2><form method='post'><div class='mb-3'><label>Client/Brand Name</label><input type='text' name='client_name' class='form-control'></div><div class='mb-3'><label>Domain</label><input type='text' name='domain' class='form-control'></div><button class='btn btn-success'>Create Instance</button></form><table class='table table-bordered mt-4'><thead><tr><th>Client</th><th>Domain</th><th>Status</th><th>Created</th></tr></thead><tbody><?php while($i = $instances->fetch_assoc()): ?><tr><td><?= htmlspecialchars($i['client_name']) ?></td><td><?= htmlspecialchars($i['domain']) ?></td><td><?= htmlspecialchars($i['status']) ?></td><td><?= $i['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Spin up new branded instances for clients. Manage white-label SaaS from one dashboard.</p></div></body></html>
