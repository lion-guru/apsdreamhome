<?php
$db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
$count = $db->query('SELECT COUNT(*) FROM ai_workflows')->fetchColumn();
echo "AI Workflows Count: $count\n";
