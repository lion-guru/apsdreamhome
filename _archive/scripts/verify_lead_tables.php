<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo in_array('property_views', $tables) ? "property_views: OK\n" : "property_views: MISSING\n";

$cols = $pdo->query('SHOW COLUMNS FROM leads')->fetchAll(PDO::FETCH_COLUMN);
echo in_array('property_interest', $cols) ? "leads.property_interest: OK\n" : "leads.property_interest: MISSING\n";
echo in_array('lead_score', $cols) ? "leads.lead_score: OK\n" : "leads.lead_score: MISSING\n";
echo in_array('source', $cols) ? "leads.source: OK\n" : "leads.source: MISSING\n";
echo in_array('budget_range', $cols) ? "leads.budget_range: OK\n" : "leads.budget_range: MISSING\n";
