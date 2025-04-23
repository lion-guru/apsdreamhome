<?php
session_start();
include 'config.php';
require_role('Admin');
$workflows = $conn->query("SELECT * FROM workflow_automations ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>External Workflow Automation (Zapier/Make)</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>External Workflow Automation</h2><form method='post'><div class='mb-3'><label>Workflow Name</label><input type='text' name='name' class='form-control'></div><div class='mb-3'><label>Provider</label><select name='provider' class='form-control'><option value='zapier'>Zapier</option><option value='make'>Make (Integromat)</option><option value='custom'>Custom</option></select></div><div class='mb-3'><label>Webhook URL</label><input type='text' name='webhook_url' class='form-control'></div><button class='btn btn-success'>Add Workflow</button></form><table class='table table-bordered mt-4'><thead><tr><th>Name</th><th>Provider</th><th>Status</th><th>Webhook</th><th>Created</th></tr></thead><tbody><?php while($w = $workflows->fetch_assoc()): ?><tr><td><?= htmlspecialchars($w['name']) ?></td><td><?= htmlspecialchars($w['provider']) ?></td><td><?= htmlspecialchars($w['status']) ?></td><td><?= htmlspecialchars($w['webhook_url']) ?></td><td><?= $w['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Integrate with Zapier, Make, or any external workflow tool. Trigger workflows via webhooks and automate business processes.</p></div></body></html>
