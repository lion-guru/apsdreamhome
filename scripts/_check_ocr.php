<?php
$d = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root','');
$cols = $d->query('SHOW COLUMNS FROM ocr_templates')->fetchAll(PDO::FETCH_COLUMN);
echo in_array('tenant_id', $cols) ? 'HAS' : 'NO';
echo "\n";

