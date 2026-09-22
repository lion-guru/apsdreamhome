<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '');
$users = $pdo->query("SELECT email, role, referral_code FROM users WHERE role IN ('associate', 'agent')")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);