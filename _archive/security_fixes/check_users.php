<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM mlm_salary_grants");
    echo "<h3>mlm_salary_grants:</h3><pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    $stmt = $pdo->query("SELECT * FROM mlm_commission_ledger WHERE commission_type = 'salary'");
    echo "<h3>mlm_commission_ledger (salary):</h3><pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
