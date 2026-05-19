<?php
file_put_contents("c:\temp\php_debug.log", 
    "[" . date("Y-m-d H:i:s") . "] METHOD=" . $_SERVER["REQUEST_METHOD"] . 
    " URI=" . $_SERVER["REQUEST_URI"] . 
    " SCRIPT=" . $_SERVER["SCRIPT_NAME"] . 
    " PHP_SELF=" . $_SERVER["PHP_SELF"] . 
    " POST=" . json_encode($_POST) . 
    "\n", FILE_APPEND
);
echo "OK";
