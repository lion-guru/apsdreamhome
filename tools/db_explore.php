<?php
echo "APS DREAM HOME - DATABASE EXPLORATION\n\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    sort($tables);
    echo "TOTAL: " . count($tables) . "\n\n";

    $keywords = ["farmer","kisan","land","colony","project","site","plot","block","sector","amenit","layout","phase",
                 "booking","sale","agreement","installment","payment_plan","registry","deed","allotment",
                 "commission","mlm","payout","referral","reward","bonus","level","network","genealogy",
                 "account","transaction","ledger","expense","income","invoice","tax","profit","loss",
                 "associate","partner","affiliate",
                 "customer","user","member","client",
                 "property","lead","inquiry","contact"];

    foreach ($keywords as $kw) {
        $matches = array_filter($tables, function($t) use ($kw) {
            return stripos($t, $kw) !== false;
        });
        if (count($matches) > 0) {
            echo "--- " . $kw . " (" . count($matches) . ") ---\n";
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
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
