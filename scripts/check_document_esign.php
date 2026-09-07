<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE document_esign');
var_dump($stmt->fetchAll());