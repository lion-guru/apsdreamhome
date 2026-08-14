<?php
$class = "Front\\\\PageController"; // This is Front\\PageController (2 backslashes)
$replaced = str_replace('\\\\', '/', $class);
echo "Replaced: '$replaced'\n";

$file = "app/Http/Controllers/" . $replaced . ".php";
echo "Looking for: $file\n";
echo file_exists($file) ? "FOUND\n" : "NOT FOUND\n";?>