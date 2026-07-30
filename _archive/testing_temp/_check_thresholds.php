<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'], $config['username'], $config['password']);
foreach($pdo->query('SELECT rank_name, min_leg_count, min_qualifying_volume, direct_sale_pct FROM mlm_rank_benefits WHERE is_active=1 ORDER BY rank_order') as $r) {
    echo str_pad($r['rank_name'],20).' legs>='.$r['min_leg_count'].' vol>='.$r['min_qualifying_volume'].' rate='.$r['direct_sale_pct'].'%'.PHP_EOL;
}
