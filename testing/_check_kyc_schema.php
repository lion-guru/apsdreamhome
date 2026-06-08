<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$cols = $pdo->query('DESCRIBE kyc_requests')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  {$c['Field']} ({$c['Type']}) {$c['Null']} {$c['Key']}\n";
}
