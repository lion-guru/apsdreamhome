<?php
$conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $conn->query("SHOW TABLES LIKE 'mlm_rank_slabs'");
if ($stmt->rowCount() > 0) {
    echo "Exists. Columns:\n";
    $cols = $conn->query("DESCRIBE mlm_rank_slabs");
    print_r($cols->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "Does not exist.";
}?>