<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$r = $pdo->query('DESCRIBE ai_lead_scores')->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo $row['Field'] . ' | ' . $row['Type'] . "\n";?>