<?php
/**
 * Idempotent seed: full AI employee roster (ops + C-suite analysts).
 *
 * Ops:    Aira(telecaller), Vivaan(qualifier), Sahil(followup), Isha(support)
 * Sales:  Arjun(sales analyst), Finance: Zoya(finance analyst)
 * HR:     Riya(hr assistant), Ops: Karan(ops coordinator)
 * C-suite: Ash(cEo analyst), Devi(cto analyst), Neha(cfo analyst), Vikram(coo analyst)
 *
 * Re-run = SKIP (INSERT IGNORE). All start paused — admin activates.
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

$db = App\Core\Database\Database::getInstance();

$roster = [
    // name, role, scope comment
    ['Vivaan',  'qualifier', ['max_leads_per_run' => 5]],
    ['Sahil',   'followup',  ['max_drafts_per_run' => 3]],
    ['Isha',    'support',   ['channels' => ['inquiry', 'ticket']]],
    ['Arjun',   'sales',     ['focus' => ['pipeline', 'win_rate']]],
    ['Zoya',    'finance',   ['focus' => ['collections', 'reminders']]],
    ['Riya',    'hr',        ['focus' => ['attendance', 'leaves']]],
    ['Karan',   'operations', ['focus' => ['funnel', 'stale_leads']]],
    ['Ash',     'ceo_analyst',  ['reports' => ['daily', 'weekly']]],
    ['Devi',    'cto_analyst',  ['reports' => ['health', 'errors']]],
    ['Neha',    'cfo_analyst',  ['reports' => ['collections', 'outstanding']]],
    ['Vikram',  'coo_analyst',  ['reports' => ['funnel', 'capacity']]],
];

foreach ($roster as [$name, $role, $scope]) {
    $db->execute(
        "INSERT IGNORE INTO ai_employees (name, role, status, scope) VALUES (?, ?, 'paused', ?)",
        [$name, $role, json_encode($scope)]
    );
}

$rows = $db->fetchAll("SELECT name, role, status FROM ai_employees ORDER BY id");
foreach ($rows as $r) {
    echo "  {$r['name']}: {$r['role']} ({$r['status']})\n";
}
echo "Roster total: " . count($rows) . "\n";
exit(0);
