<?php
// API Routes
$router->get('/api/health', 'ApiController@health');
$router->get('/api/properties', 'ApiController@properties');
$router->post('/api/contact', 'ApiController@contact');
$router->post('/api/newsletter', 'ApiController@newsletter');
$router->post('/api/property-inquiry', 'ApiController@propertyInquiry');
?>