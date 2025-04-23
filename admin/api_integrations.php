<?php
session_start();
include 'config.php';
require_role('Admin');
$integrations = $conn->query("SELECT * FROM api_integrations ORDER BY id DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>API Integrations</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Custom API Integrations</h2><form method='post'><div class='mb-3'><label>Service Name</label><input type='text' name='service_name' class='form-control'></div><div class='mb-3'><label>API Base URL</label><input type='text' name='api_url' class='form-control'></div><div class='mb-3'><label>API Key/Token</label><input type='text' name='api_key' class='form-control'></div><button class='btn btn-success'>Add Integration</button></form><table class='table table-bordered mt-4'><thead><tr><th>Service</th><th>Base URL</th><th>Status</th><th>Added</th></tr></thead><tbody><?php while($i = $integrations->fetch_assoc()): ?><tr><td><?= htmlspecialchars($i['service_name']) ?></td><td><?= htmlspecialchars($i['api_url']) ?></td><td><?= htmlspecialchars($i['status']) ?></td><td><?= $i['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Connect to any SaaS, CRM, or business tool. Test and monitor API connections from here.</p></div></body></html>
