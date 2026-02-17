<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
$contracts = $db->fetchAll("SELECT * FROM smart_contracts ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI-Driven Smart Contracts</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI-Driven Smart Contracts</h2><form method='post'><div class='mb-3'><label>Agreement Name</label><input type='text' name='agreement_name' class='form-control'></div><div class='mb-3'><label>Parties</label><input type='text' name='parties' class='form-control'></div><div class='mb-3'><label>Terms</label><textarea name='terms' class='form-control'></textarea></div><button class='btn btn-success'>Create Smart Contract</button></form><table class='table table-bordered mt-4'><thead><tr><th>Agreement</th><th>Parties</th><th>Status</th><th>Blockchain Txn</th><th>Created</th></tr></thead><tbody><?php foreach($contracts as $c): ?><tr><td><?= h($c['agreement_name']) ?></td><td><?= h($c['parties']) ?></td><td><?= h($c['status']) ?></td><td><?= h($c['blockchain_txn'] ?? '') ?></td><td><?= $c['created_at'] ?></td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*AI and blockchain integration ready. Automate agreements and transactions for compliance and transparency.</p></div></body></html>
