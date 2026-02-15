<?php
session_start();
include 'config.php';
require_role('Admin');
$contracts = $conn->query("SELECT * FROM smart_contracts ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI-Driven Smart Contracts</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI-Driven Smart Contracts</h2><form method='post'><div class='mb-3'><label>Agreement Name</label><input type='text' name='agreement_name' class='form-control'></div><div class='mb-3'><label>Parties</label><input type='text' name='parties' class='form-control'></div><div class='mb-3'><label>Terms</label><textarea name='terms' class='form-control'></textarea></div><button class='btn btn-success'>Create Smart Contract</button></form><table class='table table-bordered mt-4'><thead><tr><th>Agreement</th><th>Parties</th><th>Status</th><th>Blockchain Txn</th><th>Created</th></tr></thead><tbody><?php while($c = $contracts->fetch_assoc()): ?><tr><td><?= htmlspecialchars($c['agreement_name']) ?></td><td><?= htmlspecialchars($c['parties']) ?></td><td><?= htmlspecialchars($c['status']) ?></td><td><?= htmlspecialchars($c['blockchain_txn'] ?? '') ?></td><td><?= $c['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*AI and blockchain integration ready. Automate agreements and transactions for compliance and transparency.</p></div></body></html>
