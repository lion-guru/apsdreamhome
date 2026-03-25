<?php
$db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
$stmt = $db->query('DESCRIBE ai_workflows');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
