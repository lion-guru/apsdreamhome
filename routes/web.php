<?php

/**
 * Web Routes Configuration
 * Custom Framework Web Routes (Converted from Laravel)
 */

// Web Route Definitions
$webRoutes = [
    // Public routes (no authentication required)
    'public' => [
        'GET' => [
            '/' => 'HomeController@index',
            '/about' => 'HomeController@about',
            '/contact' => 'HomeController@contact',
            '/projects' => 'HomeController@projects',
            '/projects/{projectCode}' => 'HomeController@project',
            '/projects/city/{city}' => 'HomeController@projects',
            '/properties' => 'HomeController@projects', // Alias for projects
            '/properties/featured' => 'HomeController@featuredProperties',
            '/property/{id}' => 'HomeController@propertyDetail',
            '/services' => 'HomeController@services',
            '/team' => 'HomeController@team',
            '/careers' => 'HomeController@careers',
            '/testimonials' => 'HomeController@testimonials',
            '/blog' => 'HomeController@blog',
            '/blog/{slug}' => 'HomeController@blogShow',
            '/faq' => 'HomeController@faq',
            '/sitemap' => 'HomeController@sitemap',
            '/privacy' => 'HomeController@privacy',
            '/terms' => 'HomeController@terms',
            '/associate/login' => 'AssociateController@login',
            '/associate/logout' => 'AssociateController@logout',
            '/employee/login' => 'EmployeeController@login',
            '/employee/logout' => 'EmployeeController@logout',
            '/admin/login' => 'AdminController@login',
            '/admin/logout' => 'AdminController@logout',
            '/payment/{id}' => 'PaymentController@show',
            '/payment/success/{id}' => 'PaymentController@success',
            '/payment/cancel/{id}' => 'PaymentController@cancel',
            
            // Error page tests
            '/test/error/404' => 'App\Controllers\ErrorTestController@test404',
            '/test/error/500' => 'App\Controllers\ErrorTestController@test500',
            '/test/error/403' => 'App\Controllers\ErrorTestController@test403',
            '/test/error/401' => 'App\Controllers\ErrorTestController@test401',
            '/test/error/400' => 'App\Controllers\ErrorTestController@test400',
            '/test/error/generic' => 'App\Controllers\ErrorTestController@testGeneric',
            '/test/error/exception' => 'App\Controllers\ErrorTestController@testException',
        ],

        'POST' => [
            '/associate/authenticate' => 'AssociateController@authenticate',
            '/employee/authenticate' => 'EmployeeController@authenticate',
            '/admin/authenticate' => 'AdminController@authenticate',
            '/login' => 'AuthController@login',
            '/register' => 'AuthController@register',
            '/forgot-password' => 'AuthController@processForgotPassword',
            '/reset-password' => 'AuthController@processResetPassword',
            '/payment/{id}' => 'PaymentController@process',
        ],
    ],

    // Authenticated routes (require login)
    'authenticated' => [
        'GET' => [
            '/dashboard' => 'UserController@dashboard',
            '/profile' => 'UserController@profile',
            '/my-properties' => 'UserController@myProperties',
            '/bookmarks' => 'UserController@bookmarks',
            '/payment-history' => 'UserController@paymentHistory',
            '/leads' => 'LeadController@index',
            '/leads/{id}' => 'LeadController@show',
            '/leads/{id}/edit' => 'LeadController@edit',
            '/leads/reports' => 'LeadController@reports',
            '/farmers' => 'FarmerController@index',
            '/farmers/list' => 'FarmerController@list',
            '/farmers/create' => 'FarmerController@create',
            '/farmers/{id}' => 'FarmerController@show',
            '/farmers/{id}/edit' => 'FarmerController@edit',
            '/farmers/search' => 'FarmerController@search',
            '/farmers/state/{id}' => 'FarmerController@getByState',
        ],

        'POST' => [
            '/logout' => 'AuthController@logout',
            '/leads' => 'LeadController@store',
            '/leads/{id}/activity' => 'LeadController@addActivity',
            '/leads/{id}/note' => 'LeadController@addNote',
            '/leads/{id}/assign' => 'LeadController@assign',
            '/leads/{id}/convert' => 'LeadController@convert',
            '/farmers' => 'FarmerController@store',
        ],

        'PUT' => [
            '/profile' => 'UserController@updateProfile',
            '/leads/{id}' => 'LeadController@update',
            '/farmers/{id}' => 'FarmerController@update',
        ],

        'DELETE' => [
            '/farmers/{id}' => 'FarmerController@delete',
        ],
    ],

    // Associate routes (MLM Partner Portal - authenticated)
    'associate' => [
        'GET' => [
            '/associate/dashboard' => 'AssociateController@dashboard',
            '/associate/team' => 'AssociateController@team',
            '/associate/business' => 'AssociateController@business',
            '/associate/earnings' => 'AssociateController@earnings',
            '/associate/payouts' => 'AssociateController@payouts',
            '/associate/profile' => 'AssociateController@profile',
            '/associate/kyc' => 'AssociateController@kyc',
            '/associate/rank' => 'AssociateController@rank',
            '/associate/support' => 'AssociateController@support',
            '/associate/reports' => 'AssociateController@reports',
        ],

        'POST' => [
            '/associate/request-payout' => 'AssociateController@requestPayout',
            '/associate/update-profile' => 'AssociateController@updateProfile',
            '/associate/submit-kyc' => 'AssociateController@submitKYC',
        ],
    ],

    // Employee routes (Employee Portal - authenticated)
    'employee' => [
        'GET' => [
            '/employee/dashboard' => 'EmployeeController@dashboard',
            '/employee/profile' => 'EmployeeController@profile',
            '/employee/tasks' => 'EmployeeController@tasks',
            '/employee/attendance' => 'EmployeeController@attendance',
            '/employee/leaves' => 'EmployeeController@leaves',
            '/employee/documents' => 'EmployeeController@documents',
            '/employee/activities' => 'EmployeeController@activities',
            '/employee/performance' => 'EmployeeController@performance',
            '/employee/salary-history' => 'EmployeeController@salaryHistory',
            '/employee/reporting-structure' => 'EmployeeController@reportingStructure',
        ],

        'POST' => [
            '/employee/update-profile' => 'EmployeeController@updateProfile',
            '/employee/update-task/{id}' => 'EmployeeController@updateTask',
            '/employee/record-attendance' => 'EmployeeController@recordAttendance',
            '/employee/apply-leave' => 'EmployeeController@applyLeave',
            '/employee/change-password' => 'EmployeeController@changePassword',
        ],
    ],

    // Customer routes (Customer Portal - authenticated)
    'customer' => [
        'GET' => [
            '/customer/dashboard' => 'CustomerController@dashboard',
            '/customer/properties' => 'CustomerController@properties',
            '/customer/property/{id}' => 'CustomerController@propertyDetails',
            '/customer/favorites' => 'CustomerController@favorites',
            '/customer/bookings' => 'CustomerController@bookings',
            '/customer/payments' => 'CustomerController@payments',
            '/customer/reviews' => 'CustomerController@reviews',
            '/customer/alerts' => 'CustomerController@alerts',
            '/customer/emi-calculator' => 'CustomerController@emiCalculator',
            '/customer/property-views' => 'CustomerController@propertyViews',
            '/customer/emi-history' => 'CustomerController@emiHistory',
            '/customer/profile' => 'CustomerController@profile',
            '/customer/associate-benefits' => 'CustomerController@associateBenefits',
            '/customer/associate-invitations' => 'CustomerController@associateInvitations',
            '/customer/become-associate' => 'CustomerController@becomeAssociate',
        ],

        'POST' => [
            '/customer/toggle-favorite/{id}' => 'CustomerController@toggleFavorite',
            '/customer/submit-review/{id}' => 'CustomerController@submitReview',
            '/customer/create-alert' => 'CustomerController@createAlert',
            '/customer/calculate-emi' => 'CustomerController@calculateEMI',
            '/customer/update-profile' => 'CustomerController@updateProfile',
            '/customer/accept-invitation/{id}' => 'CustomerController@acceptInvitation',
            '/customer/send-invitation' => 'CustomerController@sendInvitation',
        ],
    ],

    // Admin routes (Admin Portal - requires admin authentication)
    'admin' => [
        'GET' => [
            '/admin' => 'AdminController@dashboard',
            '/admin/dashboard' => 'AdminController@dashboard',
            '/admin/users' => 'AdminController@users',
            '/admin/properties' => 'AdminController@properties',
            '/admin/leads' => 'AdminController@leads',
            '/admin/associates' => 'AdminController@associates',
            '/admin/customers' => 'AdminController@customers',
            '/admin/reports' => 'AdminController@reports',
            '/admin/settings' => 'AdminController@settings',
            '/admin/database' => 'AdminController@database',
            '/admin/logs' => 'AdminController@logs',
            '/admin/employees' => 'AdminController@employees',
            '/admin/employees/create' => 'AdminController@createEmployee',
            '/admin/employees/{id}' => 'AdminController@showEmployee',
            '/admin/employees/{id}/edit' => 'AdminController@editEmployee',
            '/admin/employees/department/{id}' => 'AdminController@getEmployeesByDepartment',
            '/admin/mlm-plan-builder' => 'AdminController@hybridMLMPlanBuilder',
            '/admin/mlm-analytics' => 'AdminController@mlmAnalytics',
            '/admin/export/{type}' => 'AdminController@export',
        ],

        'POST' => [
            '/admin/settings' => 'AdminController@updateSettings',
            '/admin/backup' => 'AdminController@createBackup',
            '/admin/clear-cache' => 'AdminController@clearCache',
            '/admin/employees' => 'AdminController@storeEmployee',
            '/admin/employees/{id}/deactivate' => 'AdminController@deactivateEmployee',
            '/admin/employees/{id}/reactivate' => 'AdminController@reactivateEmployee',
            '/admin/employees/{id}/tasks' => 'AdminController@createEmployeeTask',
            '/admin/employees/{id}/password' => 'AdminController@updateEmployeePassword',
            '/admin/mlm-plans' => 'AdminController@createMLMPlan',
        ],

        'PUT' => [
            '/admin/employees/{id}' => 'AdminController@updateEmployee',
        ],
    ],
];

// Register routes with the router
if (isset($app) && $app instanceof \App\Core\App) {
    $router = $app->router();
    
    // Register public routes
    foreach ($webRoutes['public'] as $method => $routes) {
        foreach ($routes as $path => $handler) {
            $method = strtoupper($method);
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler);
                    break;
                case 'POST':
                    $router->post($path, $handler);
                    break;
                case 'PUT':
                    $router->put($path, $handler);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler);
                    break;
                case 'PATCH':
                    $router->patch($path, $handler);
                    break;
                default:
                    $router->match([$method], $path, $handler);
                    break;
            }
        }
    }
    
    // Register authenticated routes with auth middleware
    foreach ($webRoutes['authenticated'] as $method => $routes) {
        foreach ($routes as $path => $handler) {
            $method = strtoupper($method);
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'POST':
                    $router->post($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PUT':
                    $router->put($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PATCH':
                    $router->patch($path, $handler, ['middleware' => ['auth']]);
                    break;
                default:
                    $router->match([$method], $path, $handler, ['middleware' => ['auth']]);
                    break;
            }
        }
    }
    
    // Register associate routes with auth middleware
    foreach ($webRoutes['associate'] as $method => $routes) {
        foreach ($routes as $path => $handler) {
            $method = strtoupper($method);
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'POST':
                    $router->post($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PUT':
                    $router->put($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PATCH':
                    $router->patch($path, $handler, ['middleware' => ['auth']]);
                    break;
                default:
                    $router->match([$method], $path, $handler, ['middleware' => ['auth']]);
                    break;
            }
        }
    }
    
    // Register employee routes with auth middleware
    foreach ($webRoutes['employee'] as $method => $routes) {
        foreach ($routes as $path => $handler) {
            $method = strtoupper($method);
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'POST':
                    $router->post($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PUT':
                    $router->put($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PATCH':
                    $router->patch($path, $handler, ['middleware' => ['auth']]);
                    break;
                default:
                    $router->match([$method], $path, $handler, ['middleware' => ['auth']]);
                    break;
            }
        }
    }
    
    // Register customer routes with auth middleware
    foreach ($webRoutes['customer'] as $method => $routes) {
        foreach ($routes as $path => $handler) {
            $method = strtoupper($method);
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'POST':
                    $router->post($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PUT':
                    $router->put($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler, ['middleware' => ['auth']]);
                    break;
                case 'PATCH':
                    $router->patch($path, $handler, ['middleware' => ['auth']]);
                    break;
                default:
                    $router->match([$method], $path, $handler, ['middleware' => ['auth']]);
                    break;
            }
        }
    }
    
    // Register admin routes with admin middleware
    foreach ($webRoutes['admin'] as $method => $routes) {
        foreach ($routes as $path => $handler) {
            $method = strtoupper($method);
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler, ['middleware' => ['admin']]);
                    break;
                case 'POST':
                    $router->post($path, $handler, ['middleware' => ['admin']]);
                    break;
                case 'PUT':
                    $router->put($path, $handler, ['middleware' => ['admin']]);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler, ['middleware' => ['admin']]);
                    break;
                case 'PATCH':
                    $router->patch($path, $handler, ['middleware' => ['admin']]);
                    break;
                default:
                    $router->match([$method], $path, $handler, ['middleware' => ['admin']]);
                    break;
            }
        }
    }
}
