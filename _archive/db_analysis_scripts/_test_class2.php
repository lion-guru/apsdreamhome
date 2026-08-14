<?php
$class = "Front\\\\PageController"; // This is Front\\PageController (2 backslashes)
echo "Class: $class\n";
echo "Length: " . strlen($class) . "\n";
echo "Hex: " . bin2hex($class) . "\n";

// Replace \\ with /
$replaced = str_replace('\\\\', '/', $class);
echo "After replace: $replaced\n";?>