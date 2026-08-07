<?php
$html = file_get_contents('http://localhost/apsdreamhome/');
echo "<pre>" . htmlspecialchars($html) . "</pre>";
