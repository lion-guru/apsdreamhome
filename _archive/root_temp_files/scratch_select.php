<?php
$conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $conn->query("SELECT * FROM mlm_rank_benefits");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
