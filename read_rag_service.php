<?php
$file = file('C:\xampp\htdocs\apsdreamhome\app/Services/AI/RAGService.php');
for ($i = 0; $i < count($file); $i++) {
    echo ($i + 1) . ": " . $file[$i];
}