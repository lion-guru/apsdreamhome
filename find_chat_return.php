<?php
$file = file('C:\xampp\htdocs\apsdreamhome\app/Http/Controllers/Api/AIAgentApiController.php');
// Find the chat() method and its return statements
for ($i = 0; $i < count($file); $i++) {
    $line = trim($file[$i]);
    if (stripos($line, "'response' =>") !== false || stripos($line, "'reply' =>") !== false || stripos($line, "'message' =>") !== false) {
        echo ($i + 1) . ": " . trim($file[$i]) . "\n";
    }
}