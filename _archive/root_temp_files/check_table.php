<?php
$conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmt = $conn->prepare('SHOW TABLES LIKE ?');
    $stmt->execute(['document_esign']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo 'Table exists';
    } else {
        echo 'Table does not exist';
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}?>