<?php
$db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
$stmt = $db->query('SELECT id, name, type, status FROM ai_workflows LIMIT 20');
$workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($workflows);
