<?php
require_once __DIR__ . '/core/init.php';
require_role('Support');
require_permission('view_support_dashboard');
$db = \App\Core\App::database();
$total_tickets = $db->fetchOne("SELECT COUNT(*) AS c FROM support_tickets")['c'];
$open_tickets = $db->fetchOne("SELECT COUNT(*) AS c FROM support_tickets WHERE status='open'")['c'];
$closed_tickets = $db->fetchOne("SELECT COUNT(*) AS c FROM support_tickets WHERE status='closed'")['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Support Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Support Dashboard</h2><div class="row mb-4"><div class="col"><div class="card p-3"><h4>Total Tickets</h4><span style="font-size:2rem;"><?= $total_tickets ?></span></div></div><div class="col"><div class="card p-3"><h4>Open Tickets</h4><span style="font-size:2rem;"><?= $open_tickets ?></span></div></div><div class="col"><div class="card p-3"><h4>Closed Tickets</h4><span style="font-size:2rem;"><?= $closed_tickets ?></span></div></div></div><a href="support_tickets.php" class="btn btn-info">View Tickets</a> <a href="add_ticket.php" class="btn btn-info">Add Ticket</a></div></body></html>
