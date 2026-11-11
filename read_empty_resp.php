<?php
$file = file('C:\xampp\htdocs\apsdreamhome\app/Services/AI/AIService.php');
for ($i = 220; $i <= 235; $i++) {
    if (isset($file[$i])) {
        echo ($i + 1) . ": " . $file[$i];
    }
}