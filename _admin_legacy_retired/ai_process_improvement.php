<?php
session_start();
include 'config.php';
require_role('Admin');
$processes = [
  ['name' => 'Lead Assignment', 'current_time' => '2h', 'ai_suggestion' => 'Automate with rules, reduce to 30m'],
  ['name' => 'Document Approval', 'current_time' => '1d', 'ai_suggestion' => 'Parallel review, reduce to 4h'],
  ['name' => 'Customer Onboarding', 'current_time' => '3d', 'ai_suggestion' => 'Self-service portal, reduce to 1d']
];
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI Process Improvement</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI for Process Improvement</h2><table class='table table-bordered'><thead><tr><th>Process</th><th>Current Time</th><th>AI Suggestion</th></tr></thead><tbody><?php foreach($processes as $p): ?><tr><td><?= htmlspecialchars($p['name']) ?></td><td><?= htmlspecialchars($p['current_time']) ?></td><td><?= htmlspecialchars($p['ai_suggestion']) ?></td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*AI integration ready. Connect with workflow analytics for real-time process optimization.</p></div></body></html>
