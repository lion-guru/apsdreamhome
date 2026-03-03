<?php
echo "PHP is working!";
echo "<br>";
echo "Time: " . date("Y-m-d H:i:s");
echo "<br>";
echo "Document Root: " . $_SERVER["DOCUMENT_ROOT"] ?? "Not set";
echo "<br>";
echo "Request URI: " . $_SERVER["REQUEST_URI"] ?? "Not set";
?>