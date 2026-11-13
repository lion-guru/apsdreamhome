<?php
/**
 * Seed the 7 default deals-pipeline stages (Session 512).
 * Idempotent: each INSERT fires ONLY when a stage with that name is absent;
 * safe to re-run after any DB restore. business-vocabulary seed (same class as
 * admin menu seeds) — probability/colour map to the pipeline semantics the
 * controller already assumes when it special-cases 'Closed Won' / 'Closed Lost'.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$stages = [
    ['New Lead',      1, 10.00,  'info'],
    ['Contacted',     2, 25.00,  'primary'],
    ['Qualified',     3, 40.00,  'primary'],
    ['Proposal',      4, 60.00,  'warning'],
    ['Negotiation',   5, 75.00,  'warning'],
    ['Closed Won',    6, 100.00, 'success'],
    ['Closed Lost',   7, 0.00,   'danger'],
];

$check = $pdo->prepare("SELECT id FROM deal_stages WHERE name = ? LIMIT 1");
$insert = $pdo->prepare("INSERT INTO deal_stages (tenant_id, name, stage_order, probability, color, is_active) VALUES (1, ?, ?, ?, ?, 1)");

foreach ($stages as $s) {
    $check->execute([$s[0]]);
    if ($check->fetch(PDO::FETCH_ASSOC)) {
        echo "SKIP stage '{$s[0]}' (already present)\n";
        continue;
    }
    $insert->execute([$s[0], $s[1], $s[2], $s[3]]);
    echo "SEEDED stage '{$s[0]}'\n";
}

$count = (int)$pdo->query("SELECT COUNT(*) FROM deal_stages")->fetchColumn();
echo "Total stages now: {$count}\n";
echo "Done.\n";
