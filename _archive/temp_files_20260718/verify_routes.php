<?php
$content = file_get_contents('C:\xampp\htdocs\apsdreamhome\routes\web.php');
echo 'PageController@ remaining: ' . substr_count($content, 'PageController@') . "\n";
echo 'PropertyController@ count: ' . substr_count($content, 'PropertyController@') . "\n";
echo 'ProjectController@ count: ' . substr_count($content, 'ProjectController@') . "\n";
echo 'ToolController@ count: ' . substr_count($content, 'ToolController@') . "\n";
echo 'LegalController@ count: ' . substr_count($content, 'LegalController@') . "\n";
echo 'CareerController@ count: ' . substr_count($content, 'CareerController@') . "\n";
echo 'FinancialController@ count: ' . substr_count($content, 'FinancialController@') . "\n";
echo 'ServiceController@ count: ' . substr_count($content, 'ServiceController@') . "\n";
echo 'AIController@ count: ' . substr_count($content, 'AIController@') . "\n";
echo 'ContactController@ count: ' . substr_count($content, 'ContactController@') . "\n";
echo 'UserDashboardController@ count: ' . substr_count($content, 'UserDashboardController@') . "\n";
echo 'AssociateController@ count: ' . substr_count($content, 'AssociateController@') . "\n";?>