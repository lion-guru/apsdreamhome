<?php
$lines = file("C:/xampp/htdocs/apsdreamhome/app/Services/HybridCommissionEngine.php");
foreach ($lines as $i => $line) {
    if (stripos($line, 'writeLedger') !== false) {
        echo ($i+1) . ": " . trim($line) . "\n";
    }
}
