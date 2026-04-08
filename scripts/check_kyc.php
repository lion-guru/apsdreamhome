<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$tables = ['kyc_details', 'kyc_documents', 'kyc_verification'];
foreach ($tables as $t) {
    echo "$t:\n";
    try {
        $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";
    } catch (Exception $e) { echo "  ERROR: {$e->getMessage()}\n"; }
}
