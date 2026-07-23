<?php
require 'c:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
require 'c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/BaseController.php';
require 'c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Front/PageController.php';
$methods = get_class_methods('App\Http\Controllers\Front\PageController');
echo implode("\n", $methods);
