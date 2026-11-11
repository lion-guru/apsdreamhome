<?php
// Read the chat method from AIAgentApiController to check response format
$file = file('C:\xampp\htdocs\apsdreamhome\app/Http/Controllers/Api/AIAgentApiController.php');
for ($i = 95; $i <= 135; $i++) {
    if (isset($file[$i])) {
        echo ($i + 1) . ": " . $file[$i];
    }
}