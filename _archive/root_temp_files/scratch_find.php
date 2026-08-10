<?php
$files = glob("C:\\xampp\\htdocs\\apsdreamhome\\*{unified,login,register}*.php", GLOB_BRACE);
$files = array_merge($files, glob("C:\\xampp\\htdocs\\apsdreamhome\\*\\*{unified,login,register}*.php", GLOB_BRACE));
print_r($files);
