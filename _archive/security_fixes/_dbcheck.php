<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = ['colonies','plots','plot_bookings','bookings','users','districts','states','mlm_network_tree','colony_layouts','plot_allocations','colony_development_costs'];
foreach($tables as $t) {
    try { $r = $pdo->query("SELECT COUNT(*) as c FROM $t"); echo "$t: " . $r->fetch()['c'] . "\n"; }
    catch(Exception $e) { echo "$t: NOT FOUND\n"; }
}
$r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE role='customer'"); echo "customers: " . $r->fetch()['c'] . "\n";
$r = $pdo->query("SELECT COUNT(*) as c FROM users WHERE role='associate'"); echo "associates: " . $r->fetch()['c'] . "\n";
$r = $pdo->query("SELECT id,name,is_active,total_plots,available_plots FROM colonies ORDER BY id");
while($row = $r->fetch()) echo "colony: id={$row['id']} name={$row['name']} active={$row['is_active']} total_plots={$row['total_plots']} available={$row['available_plots']}\n";
$r = $pdo->query("SELECT status,COUNT(*) as c FROM plots GROUP BY status");
while($row = $r->fetch()) echo "plot_status: {$row['status']}={$row['c']}\n";
$r = $pdo->query("SELECT COUNT(*) as c FROM colony_layouts"); echo "layouts: " . $r->fetch()['c'] . "\n";
$r = $pdo->query("SELECT COUNT(*) as c FROM colony_development_costs"); echo "dev_costs: " . $r->fetch()['c'] . "\n";
?>
