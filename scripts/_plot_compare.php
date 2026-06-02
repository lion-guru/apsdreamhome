<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
foreach (['inventory_plots', 'plot_master', 'plots'] as $t) {
    echo PHP_EOL . "=== $t ===" . PHP_EOL;
    foreach ($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  " . $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
    }
}
echo PHP_EOL . "=== ai_tools_directory (sample) ===" . PHP_EOL;
foreach ($pdo->query("SELECT * FROM ai_tools_directory LIMIT 3")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    print_r($r);
}
