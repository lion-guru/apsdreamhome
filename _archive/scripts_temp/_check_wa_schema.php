<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
foreach($db->query('DESCRIBE whatsapp_templates') as $row) {
    echo $row['Field'] . ' | ' . $row['Type'] . PHP_EOL;
}?>