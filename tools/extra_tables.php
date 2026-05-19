<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$keywords = ["registr", "deed", "agreement", "allotment", "reservation", "installment", "emi", "payment_plan", "cost", "develop", "infrastructure", "road", "park", "school", "amenit", "stock", "inventory", "stakeholder", "investor", "budget", "revenue", "receipt", "purchase", "supplier", "vendor", "material", "contractor", "document", "legal"];
foreach ($keywords as $kw) {
    $matches = array_filter($tables, fn($t) => stripos($t, $kw) !== false);
    if (count($matches) > 0) {
        echo "--- " . $kw . " ---\n";
        foreach ($matches as $m) {
            echo "  TABLE: " . $m . "\n";
            $colStmt = $pdo->query("SHOW COLUMNS FROM `" . $m . "`");
            $cols = $colStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                $pk = $c["Key"] === "PRI" ? "PK" : "";
                echo "    [" . $c["Field"] . "] " . $c["Type"] . " " . $pk . "\n";
            }
            echo "\n";
        }
    }
}
