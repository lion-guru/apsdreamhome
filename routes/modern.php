<?php

/**
 * Modern Routes Configuration
 * Unified routing system with modern App pipeline
 */

use App\Core\App;

// Make $app available for route registration

/** @var App $app */

// Modern route definitions with improved structure
$app->router()->group(['prefix' => 'api'], function ($router) {
    // API routes with proper REST structure
    $router->get('/properties', 'Api\PropertyController@index');
    $router->get('/properties/{id}', 'Api\PropertyController@show');
    $router->post('/properties', 'Api\PropertyController@store');
    $router->put('/properties/{id}', 'Api\PropertyController@update');
    $router->delete('/properties/{id}', 'Api\PropertyController@destroy');

    // Authentication routes
    $router->post('/auth/login', 'Api\AuthController@login');
    $router->post('/auth/register', 'Api\AuthController@register');
    $router->post('/auth/logout', 'Api\AuthController@logout');

    // User routes (protected)
    $router->group(['middleware' => 'auth'], function ($router) {
        $router->get('/user/profile', 'Api\UserController@profile');
        $router->put('/user/profile', 'Api\UserController@updateProfile');
        $router->get('/user/bookmarks', 'Api\UserController@bookmarks');
    });
});

// System Automation Routes
$app->router()->group(['prefix' => 'system'], function ($router) {
    $router->get('/cron/daily', 'System\CronController@daily');
});

// Modern web routes with better organization
$app->router()->group(['middleware' => 'web'], function ($router) {
    // Admin Login Routes (Public)
    $router->get('/admin/login', 'Auth\AdminAuthController@showLogin');
    $router->post('/admin/login', ['middleware' => 'throttle_login', 'uses' => 'Auth\AdminAuthController@processLogin']);
    $router->post('/admin/logout', 'Auth\AdminAuthController@logout');

    // Public routes
    $router->get('/', 'HomeController@index');
    $router->get('/about', 'Public\PageController@about');
    $router->get('/contact', 'Public\PageController@contact');
    $router->post('/contact', 'Public\PageController@processContact');
    $router->get('/careers', 'Public\PageController@careers');
    $router->get('/news', 'Public\PageController@news');
    $router->get('/gallery', 'Public\PageController@gallery');
    $router->get('/resell', 'Public\PageController@resell');
    $router->get('/services', 'Public\PageController@services');
    $router->get('/legal-services', 'Public\PageController@legalServices');
    $router->get('/team', 'Public\PageController@team');
    $router->get('/testimonials', 'Public\PageController@testimonials');
    $router->get('/faq', 'Public\PageController@faq');

    // Project routes
    $router->get('/projects', 'HomeController@projects');
    $router->get('/projects/{id}', 'Public\ProjectController@show');
    $router->get('/projects/city/{city}', 'HomeController@projects');

    // Property routes
    $router->get('/properties', 'Property\PropertyController@index');
    $router->get('/properties/{id}', 'Property\PropertyController@show');
    $router->get('/properties/city/{city}', 'Property\PropertyController@byCity');
    $router->get('/properties/featured', 'Property\PropertyController@featured');

    // Blog routes
    $router->get('/blog', 'Public\PageController@blog');
    $router->get('/blog/{slug}', 'Public\PageController@blogShow');

    // Utility & Footer Pages
    $router->get('/sitemap', 'Public\PageController@sitemap');
    $router->get('/privacy', 'Public\PageController@privacy');
    $router->get('/privacy-settings', 'Public\PageController@privacy');
    $router->get('/terms', 'Public\PageController@terms');
    $router->get('/downloads', 'Public\PageController@downloads');
    $router->get('/notifications', 'User\DashboardController@notifications');

    // Payment Routes
    $router->get('/payment/{id}', 'Payment\PaymentController@show');
    $router->get('/payment/success/{id}', 'Payment\PaymentController@success');
    $router->get('/payment/cancel/{id}', 'Payment\PaymentController@cancel');
    $router->post('/payment/{id}', 'Payment\PaymentController@process');

    // Associate Authentication
    $router->get('/associate/login', 'AssociateController@login');
    $router->get('/associate/register', 'AssociateController@register');
    $router->post('/associate/store', 'AssociateController@store');
    $router->get('/associate/logout', 'AssociateController@logout');
    $router->post('/associate/authenticate', 'AssociateController@authenticate');

    // Agent Authentication
    $router->get('/agent/login', 'AgentController@login');
    $router->get('/agent/logout', 'AgentController@logout');

    // Employee Authentication
    $router->get('/employee/login', 'Employee\EmployeeController@login');
    $router->get('/employee/logout', 'Employee\EmployeeController@logout');
    $router->post('/employee/authenticate', 'Employee\EmployeeController@authenticate');

    // Lead Capture
    $router->post('/quick-lead', 'Public\LeadController@storeQuick');
    $router->post('/update-lead', 'Public\LeadController@updateProgressive');

    // Error Testing (Dev only)
    if (getenv('APP_ENV') !== 'production') {
        $router->get('/test/error/404', 'Utility\ErrorTestController@test404');
        $router->get('/test/error/500', 'Utility\ErrorTestController@test500');
        $router->get('/test/error/403', 'Utility\ErrorTestController@test403');
        $router->get('/test/error/401', 'Utility\ErrorTestController@test401');
        $router->get('/test/error/400', 'Utility\ErrorTestController@test400');
        $router->get('/test/error/generic', 'Utility\ErrorTestController@testGeneric');
        $router->get('/test/error/exception', 'Utility\ErrorTestController@testException');
    }

    // Authentication routes
    $router->get('/login', 'Public\AuthController@login');
    $router->post('/login', 'Public\AuthController@processLogin');
    $router->post('/logout', 'Public\AuthController@logout');
    $router->get('/register', 'Public\AuthController@register');
    $router->post('/register', 'Public\AuthController@processRegister');
    $router->post('/forgot-password', 'AuthController@processForgotPassword');
    $router->post('/reset-password', 'AuthController@processResetPassword');

    // Protected routes
    $router->group(['middleware' => 'auth'], function ($router) {
        $router->get('/dashboard', 'User\DashboardController@index');
        $router->get('/profile', 'User\UserController@profile');
        $router->put('/profile', 'User\UserController@updateProfile');
        $router->get('/bookmarks', 'User\UserController@bookmarks');
        $router->post('/bookmarks/{id}', 'User\UserController@addBookmark');
        $router->delete('/bookmarks/{id}', 'User\UserController@removeBookmark');
    });

    // Associate Routes (MLM Partner Portal)
    $router->group(['prefix' => 'associate', 'middleware' => 'auth'], function ($router) {
        $router->get('/dashboard', 'AssociateController@dashboard');
        $router->get('/team', 'AssociateController@team');
        $router->get('/business', 'AssociateController@business');
        $router->get('/earnings', 'AssociateController@earnings');
        $router->get('/payouts', 'AssociateController@payouts');
        $router->get('/profile', 'AssociateController@profile');
        $router->get('/kyc', 'AssociateController@kyc');
        $router->get('/rank', 'AssociateController@rank');
        $router->get('/support', 'AssociateController@support');
        $router->get('/reports', 'AssociateController@reports');
        $router->get('/leads', 'AssociateController@leads');
        $router->get('/crm', 'AssociateController@crm');
        $router->get('/expenses', 'AssociateController@expenses');
        $router->get('/api/pincode', 'AssociateController@lookupPincode');
        $router->get('/api/ifsc', 'AssociateController@lookupIFSC');

        $router->post('/request-payout', 'AssociateController@requestPayout');
        $router->post('/update-profile', 'AssociateController@updateProfile');
        $router->post('/submit-kyc', 'AssociateController@submitKYC');
        $router->post('/leads/add', 'AssociateController@addLead');
        $router->post('/expenses/store', 'AssociateController@storeExpense');

        // CRM Actions
        $router->post('/crm/lead/store', 'AssociateController@storeLead');
        $router->post('/crm/lead/update', 'AssociateController@updateLead');
        $router->post('/crm/customer/store', 'AssociateController@storeCustomer');
        $router->post('/crm/note/store', 'AssociateController@storeNote');
        $router->post('/crm/activity/store', 'AssociateController@storeActivity');
        $router->post('/crm/appointment/store', 'AssociateController@storeAppointment');
        $router->post('/crm/visit/store', 'AssociateController@storeFieldVisit');
        $router->post('/crm/visit/client-location', 'AssociateController@storeClientVisitLocation');
        $router->post('/crm/visit/schedule', 'AssociateController@scheduleClientVisit');
        $router->post('/crm/message/send', 'AssociateController@sendMessage');
    });

    // Employee Routes (Employee Portal)
    $router->group(['prefix' => 'employee', 'middleware' => 'auth'], function ($router) {
        $router->get('/dashboard', 'Employee\EmployeeController@dashboard');
        $router->get('/profile', 'Employee\EmployeeController@profile');
        $router->get('/tasks', 'Employee\EmployeeController@tasks');
        $router->get('/attendance', 'Employee\EmployeeController@attendance');
        $router->get('/leaves', 'Employee\EmployeeController@leaves');
        $router->get('/documents', 'Employee\EmployeeController@documents');
        $router->get('/activities', 'Employee\EmployeeController@activities');
        $router->get('/performance', 'Employee\EmployeeController@performance');
        $router->get('/salary-history', 'Employee\EmployeeController@salaryHistory');
        $router->get('/reporting-structure', 'Employee\EmployeeController@reportingStructure');

        $router->post('/update-profile', 'Employee\EmployeeController@updateProfile');
        $router->post('/update-task/{id}', 'Employee\EmployeeController@updateTask');
        $router->post('/record-attendance', 'Employee\EmployeeController@recordAttendance');
        $router->post('/apply-leave', 'Employee\EmployeeController@applyLeave');
        $router->post('/change-password', 'Employee\EmployeeController@changePassword');

        // Attendance API Routes
        $router->post('/attendance/check-in', 'Employee\EmployeeController@checkIn');
        $router->post('/attendance/check-out', 'Employee\EmployeeController@checkOut');
        $router->get('/attendance/status', 'Employee\EmployeeController@getAttendanceStatus');
        $router->get('/attendance/history', 'Employee\EmployeeController@getAttendanceHistory');
        $router->get('/attendance/stats', 'Employee\EmployeeController@getAttendanceStats');

        // Leave API Routes
        $router->post('/leave/apply', 'Employee\EmployeeController@applyLeave');
        $router->get('/leave/balance', 'Employee\EmployeeController@getLeaveBalance');
        $router->get('/leave/calendar', 'Employee\EmployeeController@getLeaveCalendar');
        $router->post('/leave/cancel', 'Employee\EmployeeController@cancelLeave');

        // Document API Routes
        $router->post('/documents/upload', 'Employee\EmployeeController@uploadDocument');
        $router->get('/documents/download/{id}', 'Employee\EmployeeController@downloadDocument');
        $router->get('/documents/categories', 'Employee\EmployeeController@getDocumentCategories');
        $router->get('/documents/list', 'Employee\EmployeeController@getDocuments');
        $router->post('/documents/delete', 'Employee\EmployeeController@deleteDocument');
        $router->get('/documents/stats', 'Employee\EmployeeController@getDocumentStats');
    });

    // Customer Routes (Customer Portal)
    $router->group(['prefix' => 'customer', 'middleware' => 'auth'], function ($router) {
        $router->get('/dashboard', 'Customer\CustomerController@dashboard');
        $router->get('/properties', 'Customer\CustomerController@properties');
        $router->get('/property/{id}', 'Customer\CustomerController@propertyDetails');
        $router->get('/favorites', 'Customer\CustomerController@favorites');
        $router->get('/bookings', 'Customer\CustomerController@bookings');
        $router->get('/payments', 'Customer\CustomerController@payments');
        $router->get('/reviews', 'Customer\CustomerController@reviews');
        $router->get('/alerts', 'Customer\CustomerController@alerts');
        $router->get('/emi-calculator', 'Customer\CustomerController@emiCalculator');
        $router->get('/property-views', 'Customer\CustomerController@propertyViews');
        $router->get('/emi-history', 'Customer\CustomerController@emiHistory');
        $router->get('/profile', 'Customer\CustomerController@profile');
        $router->get('/associate-benefits', 'Customer\CustomerController@associateBenefits');
        $router->get('/associate-invitations', 'Customer\CustomerController@associateInvitations');
        $router->get('/become-associate', 'Customer\CustomerController@becomeAssociate');

        $router->post('/toggle-favorite/{id}', 'Customer\CustomerController@toggleFavorite');
        $router->post('/submit-review/{id}', 'Customer\CustomerController@submitReview');
        $router->post('/create-alert', 'Customer\CustomerController@createAlert');
        $router->post('/calculate-emi', 'Customer\CustomerController@calculateEMI');
        $router->post('/update-profile', 'Customer\CustomerController@updateProfile');
        $router->post('/accept-invitation/{id}', 'Customer\CustomerController@acceptInvitation');
        $router->post('/send-invitation', 'Customer\CustomerController@sendInvitation');
        $router->get('/logout', 'Customer\CustomerController@logout');
    });

    // Generic Authenticated Routes (Migrated from web.php)
    $router->group(['middleware' => 'auth'], function ($router) {
        $router->get('/my-properties', 'UserController@myProperties');
        $router->get('/payment-history', 'UserController@paymentHistory');

        // Farmers Management
        $router->get('/farmers', 'User\FarmerController@index');
        $router->get('/farmers/list', 'User\FarmerController@list');
        $router->get('/farmers/create', 'User\FarmerController@create');
        $router->get('/farmers/{id}', 'User\FarmerController@show');
        $router->get('/farmers/{id}/edit', 'User\FarmerController@edit');
        $router->get('/farmers/search', 'User\FarmerController@search');
        $router->get('/farmers/state/{id}', 'User\FarmerController@getByState');
        $router->post('/farmers', 'User\FarmerController@store');
        $router->put('/farmers/{id}', 'User\FarmerController@update');
        $router->delete('/farmers/{id}', 'User\FarmerController@delete');
    });

    // Admin Dashboards
    $router->group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function ($router) {
        $router->get('/', 'Admin\AdminController@index');
        $router->get('/dashboard', 'Admin\AdminController@dashboard');

        // System Administration
        $router->get('/export/{type}', 'Admin\AdminController@export');
        $router->post('/backup', 'Admin\AdminController@createBackup');
        $router->post('/clear-cache', 'Admin\AdminController@clearCache');
        $router->get('/logs', 'Admin\AdminController@logs');
        $router->get('/database', 'Admin\AdminController@database');

        // MLM Management
        $router->get('/mlm-plan-builder', 'Admin\AdminController@hybridMLMPlanBuilder');
        $router->get('/mlm-analytics', 'Admin\AdminController@mlmAnalytics');
        $router->post('/mlm-plans', 'Admin\AdminController@createMLMPlan');

        // Employee Management (Admin)
        $router->get('/employees', 'Admin\AdminController@employees');
        $router->get('/employees/create', 'Admin\AdminController@createEmployee');
        $router->get('/employees/{id}', 'Admin\AdminController@showEmployee');
        $router->get('/employees/{id}/edit', 'Admin\AdminController@editEmployee');
        $router->get('/employees/department/{id}', 'Admin\AdminController@getEmployeesByDepartment');
        $router->post('/employees', 'Admin\AdminController@storeEmployee');
        $router->post('/employees/{id}/deactivate', 'Admin\AdminController@deactivateEmployee');
        $router->post('/employees/{id}/reactivate', 'Admin\AdminController@reactivateEmployee');
        $router->post('/employees/{id}/tasks', 'Admin\AdminController@createEmployeeTask');
        $router->post('/employees/{id}/password', 'Admin\AdminController@updateEmployeePassword');
        $router->put('/employees/{id}', 'Admin\AdminController@updateEmployee');

        // Lead Management
        $router->get('/leads', 'Admin\LeadController@index');
        $router->get('/leads/create', 'Admin\LeadController@create');
        $router->post('/leads/store', 'Admin\LeadController@store');
        $router->get('/leads/{id}', 'Admin\LeadController@show');
        $router->get('/leads/edit/{id}', 'Admin\LeadController@edit');
        $router->post('/leads/update/{id}', 'Admin\LeadController@update');
        $router->get('/leads/delete/{id}', 'Admin\LeadController@destroy');

        // Land Management
        $router->get('/land', 'Admin\LandController@index');
        $router->get('/land/create', 'Admin\LandController@create');
        $router->post('/land/store', 'Admin\LandController@store');
        $router->get('/land/edit/{id}', 'Admin\LandController@edit');
        $router->post('/land/update/{id}', 'Admin\LandController@update');
        $router->get('/land/delete/{id}', 'Admin\LandController@destroy');
        $router->get('/land/transactions/create', 'Admin\LandController@createTransaction');
        $router->post('/land/transactions/store', 'Admin\LandController@storeTransaction');
        $router->get('/land/transactions/{id}', 'Admin\LandController@transactions');

        // Support Ticket Management
        $router->get('/tickets', 'Admin\SupportTicketController@index');
        $router->get('/tickets/create', 'Admin\SupportTicketController@create');
        $router->post('/tickets/store', 'Admin\SupportTicketController@store');
        $router->get('/tickets/{id}', 'Admin\SupportTicketController@show');
        $router->post('/tickets/reply/{id}', 'Admin\SupportTicketController@reply');
        $router->post('/tickets/updateStatus/{id}', 'Admin\SupportTicketController@updateStatus');

        // Accounting
        $router->get('/accounting', 'Admin\AccountingController@index');
        $router->get('/accounting/income/add', 'Admin\AccountingController@addIncome');
        $router->post('/accounting/income/store', 'Admin\AccountingController@storeIncome');
        $router->get('/accounting/expenses/add', 'Admin\AccountingController@addExpense');
        $router->post('/accounting/expenses/store', 'Admin\AccountingController@storeExpense');
        $router->get('/accounting/transactions', 'Admin\AccountingController@transactions');

        // AI Hub
        $router->get('/ai/hub', 'Admin\AiController@hub');
        $router->get('/ai/agent', 'Admin\AiController@agent');
        $router->get('/ai/lead-scoring', 'Admin\AiController@leadScoring');


        // Accounting Module
        $router->get('/accounting', 'Admin\AccountingController@index');

        // AI Hub Module
        $router->get('/ai/hub', 'Admin\AiController@hub');

        // News Management
        $router->get('/news', 'Admin\NewsController@index');
        $router->get('/news/create', 'Admin\NewsController@create');
        $router->post('/news/store', 'Admin\NewsController@store');
        $router->get('/news/edit/{id}', 'Admin\NewsController@edit');
        $router->post('/news/update/{id}', 'Admin\NewsController@update');
        $router->post('/news/delete/{id}', 'Admin\NewsController@delete');

        // Career Management
        $router->get('/careers', 'Admin\CareerController@index');
        $router->get('/careers/create', 'Admin\CareerController@create');
        $router->post('/careers/store', 'Admin\CareerController@store');
        $router->get('/careers/edit/{id}', 'Admin\CareerController@edit');
        $router->post('/careers/update/{id}', 'Admin\CareerController@update');
        $router->post('/careers/delete/{id}', 'Admin\CareerController@delete');
        $router->get('/careers/applications/{id}', 'Admin\CareerController@applications');
        $router->get('/careers/applications', 'Admin\CareerController@applications');

        // Media Library
        $router->get('/media', 'Admin\MediaController@index');
        $router->get('/media/create', 'Admin\MediaController@create');
        $router->post('/media/store', 'Admin\MediaController@store');
        $router->post('/media/delete/{id}', 'Admin\MediaController@delete');

        // Property Management
        $router->get('/properties', 'Admin\PropertyController@index');
        $router->get('/properties/create', 'Admin\PropertyController@create');
        $router->post('/properties/store', 'Admin\PropertyController@store');
        $router->get('/properties/edit/{id}', 'Admin\PropertyController@edit');
        $router->post('/properties/update/{id}', 'Admin\PropertyController@update');
        $router->get('/properties/delete/{id}', 'Admin\PropertyController@delete');

        // User Management
        $router->get('/users', 'Admin\UserController@index');
        $router->get('/users/create', 'Admin\UserController@create');
        $router->post('/users/store', 'Admin\UserController@store');
        $router->get('/users/edit/{id}', 'Admin\UserController@edit');
        $router->post('/users/update/{id}', 'Admin\UserController@update');
        $router->post('/users/delete/{id}', 'Admin\UserController@destroy');

        // Project Management
        $router->get('/projects', 'Admin\ProjectController@index');

        // Payment Management
        $router->get('/payments', 'Admin\PaymentController@index');
        $router->get('/payments/data', 'Admin\PaymentController@data'); // DataTables AJAX
        $router->get('/payments/customers', 'Admin\PaymentController@customers'); // Select2 AJAX
        $router->get('/payments/create', 'Admin\PaymentController@create');
        $router->post('/payments/store', 'Admin\PaymentController@store');
        $router->get('/payments/edit/{id}', 'Admin\PaymentController@edit');
        $router->post('/payments/update/{id}', 'Admin\PaymentController@update');
        $router->post('/payments/delete/{id}', 'Admin\PaymentController@destroy');
        $router->get('/payments/receipt/{id}', 'Admin\PaymentController@receipt');
        $router->get('/payments/show/{id}', 'Admin\PaymentController@show');

        // Static Pages
        $router->get('/about', 'Admin\AdminController@about');
        $router->get('/about/create', 'Admin\AdminController@aboutCreate');
        $router->post('/about/store', 'Admin\AdminController@aboutStore');
        $router->get('/about/edit/{id}', 'Admin\AdminController@aboutEdit');
        $router->post('/about/update/{id}', 'Admin\AdminController@aboutUpdate');
        $router->post('/about/delete/{id}', 'Admin\AdminController@aboutDelete');

        $router->get('/contact', 'Admin\AdminController@contact');

        // CRM Management
        $router->get('/crm/dashboard', 'Admin\AdminController@crmDashboard');

        // Task Management
        $router->get('/tasks', 'Admin\TaskController@index');
        $router->post('/tasks/store', 'Admin\TaskController@store');
        $router->get('/tasks/edit/{id}', 'Admin\TaskController@edit');
        $router->post('/tasks/update/{id}', 'Admin\TaskController@update');
        $router->get('/tasks/delete/{id}', 'Admin\TaskController@destroy');

        // Support Tickets
        $router->get('/support-tickets', 'Admin\SupportTicketController@index');
        $router->get('/support-tickets/create', 'Admin\SupportTicketController@create');
        $router->post('/support-tickets/store', 'Admin\SupportTicketController@store');
        $router->get('/support-tickets/edit/{id}', 'Admin\SupportTicketController@edit');
        $router->post('/support-tickets/update/{id}', 'Admin\SupportTicketController@update');
        $router->get('/support-tickets/delete/{id}', 'Admin\SupportTicketController@destroy');
        $router->get('/customers', 'Admin\CustomerController@index');
        $router->get('/customers/create', 'Admin\CustomerController@create');
        $router->post('/customers/store', 'Admin\CustomerController@store');
        $router->get('/customers/edit/{id}', 'Admin\CustomerController@edit');
        $router->post('/customers/update/{id}', 'Admin\CustomerController@update');
        $router->post('/customers/delete/{id}', 'Admin\CustomerController@destroy');

        $router->get('/bookings', 'Admin\BookingController@index');
        $router->get('/bookings/create', 'Admin\BookingController@create');
        $router->post('/bookings/store', 'Admin\BookingController@store');
        $router->get('/bookings/edit/{id}', 'Admin\BookingController@edit');
        $router->post('/bookings/update/{id}', 'Admin\BookingController@update');
        $router->post('/bookings/delete/{id}', 'Admin\BookingController@destroy');

        $router->get('/employees', 'Admin\EmployeeController@index');
        $router->get('/employees/create', 'Admin\EmployeeController@create');
        $router->post('/employees/store', 'Admin\EmployeeController@store');
        $router->get('/employees/edit/{id}', 'Admin\EmployeeController@edit');
        $router->post('/employees/update/{id}', 'Admin\EmployeeController@update');
        $router->post('/employees/delete/{id}', 'Admin\EmployeeController@destroy');

        $router->get('/reports', 'Analytics\ReportController@index');
        $router->get('/settings', 'Admin\AdminController@settings');

        // AI Hub Routes
        $router->get('/ai/hub', 'Admin\AdminController@aiHub');
        $router->get('/ai/agent', 'Admin\AdminController@aiAgentDashboard');
        $router->get('/ai/lead-scoring', 'Admin\AdminController@aiLeadScoring');

        // Superadmin Routes
        $router->get('/superadmin', 'Admin\AdminController@superadminDashboard');
        $router->get('/settings/whatsapp', 'Admin\AdminController@whatsappSettings');
        $router->get('/settings/site', 'Admin\AdminController@siteSettings');
        $router->get('/settings/api', 'Admin\AdminController@apiSettings');
        $router->get('/settings/backup', 'Admin\AdminController@backupSettings');
        $router->get('/settings/logs', 'Admin\AdminController@auditLogs');

        // Kisaan (Land) Management
        $router->get('/kisaan/list', 'Admin\LandController@index');
        $router->get('/kisaan/add', 'Admin\LandController@create');
        $router->post('/kisaan/store', 'Admin\LandController@store');
        $router->get('/kisaan/edit/{id}', 'Admin\LandController@edit');
        $router->post('/kisaan/update/{id}', 'Admin\LandController@update');
        $router->post('/kisaan/delete', 'Admin\LandController@destroy');

        // Gata Management
        $router->get('/gata/list', 'Admin\LandController@gataIndex');
        $router->get('/gata/add', 'Admin\LandController@gataCreate');
        $router->post('/gata/store', 'Admin\LandController@gataStore');
        $router->get('/gata/edit/{id}', 'Admin\LandController@gataEdit');
        $router->post('/gata/update/{id}', 'Admin\LandController@gataUpdate');

        // Land AJAX Helpers
        $router->get('/land/get-farmers', 'Admin\LandController@getFarmers');
        $router->get('/land/get-gata', 'Admin\LandController@getGata');

        // MLM Management Routes (Clean structure)
        $router->get('/mlm/reports', 'Admin\AdminController@mlmReports');
        $router->get('/mlm/settings', 'Admin\AdminController@mlmSettings');
        $router->get('/mlm/payouts', 'Admin\AdminController@mlmPayouts');
        $router->get('/mlm/commissions', 'Admin\AdminController@mlmCommissions');

        // Associates Management
        $router->get('/associates', 'Admin\AssociateController@index');
        $router->get('/associates/create', 'Admin\AssociateController@create');
        $router->post('/associates/store', 'Admin\AssociateController@store');
        $router->get('/associates/edit/{id}', 'Admin\AssociateController@edit');
        $router->post('/associates/update/{id}', 'Admin\AssociateController@update');
        $router->post('/associates/delete/{id}', 'Admin\AssociateController@destroy');

        // Payment Management (Consolidated above)


        // EMI Routes
        $router->get('/emi', 'Admin\EMIController@index');
        $router->get('/emi/create', 'Admin\EMIController@create');
        $router->post('/emi', 'Admin\EMIController@store');
        $router->get('/emi/stats', 'Admin\EMIController@stats');
        $router->post('/emi/list', 'Admin\EMIController@list');

        // Foreclosure
        $router->get('/emi/foreclosure-report', 'Admin\EMIController@foreclosureReport');
        $router->get('/emi/foreclosure-stats', 'Admin\EMIController@getForeclosureStats');
        $router->get('/emi/foreclosure-trend', 'Admin\EMIController@getForeclosureTrend');
        $router->get('/emi/foreclosure-data', 'Admin\EMIController@getForeclosureReportData');
        $router->get('/emi/foreclosure-amount/{id}', 'Admin\EMIController@getForeclosureAmount');
        $router->post('/emi/foreclose', 'Admin\EMIController@foreclose');

        // Operations
        $router->post('/emi/pay', 'Admin\EMIController@pay');
        $router->get('/emi/receipt/{id}', 'Admin\EMIController@generateReceipt');
        $router->post('/emi/run-automation', 'Admin\EMIController@runAutomation');

        // Detail view
        $router->get('/emi/{id}', 'Admin\EMIController@show');

        // Visit Management Routes
        $router->get('/visits', 'Admin\VisitController@index');
        $router->get('/visits/create', 'Admin\VisitController@create');
        $router->post('/visits', 'Admin\VisitController@store');
        $router->post('/visits/{id}/status', 'Admin\VisitController@updateStatus');

        // Legacy MLM routes (preserved for compatibility)
        $router->get('/mlm-analytics', 'Admin\AnalyticsController@index');
        $router->get('/mlm-analytics/data', 'Admin\AnalyticsController@data');
        $router->get('/mlm-analytics/ledger', 'Admin\AnalyticsController@ledger');
        $router->get('/mlm-analytics/export', 'Admin\AnalyticsController@export');

        $router->get('/mlm-network', 'Admin\NetworkController@index');
        $router->get('/mlm-network/search', 'Admin\NetworkController@searchUsers');
        $router->get('/mlm-network/tree', 'Admin\NetworkController@networkTree');
        $router->get('/mlm-network/agreements', 'Admin\NetworkController@listAgreements');
        $router->post('/mlm-network/agreements/create', 'Admin\NetworkController@createAgreement');
        $router->post('/mlm-network/agreements/update', 'Admin\NetworkController@updateAgreement');
        $router->post('/mlm-network/agreements/delete', 'Admin\NetworkController@deleteAgreement');
        $router->post('/mlm-network/rebuild', 'Admin\NetworkController@rebuildNetwork');

        $router->get('/mlm-payouts', 'Admin\PayoutController@index');
        $router->get('/mlm-payouts/list', 'Admin\PayoutController@list');
        $router->get('/mlm-payouts/items', 'Admin\PayoutController@items');
        $router->get('/mlm-payouts/export', 'Admin\PayoutController@export');
        $router->post('/mlm-payouts/create', 'Admin\PayoutController@create');
        $router->post('/mlm-payouts/approve', 'Admin\PayoutController@approve');
        $router->post('/mlm-payouts/disburse', 'Admin\PayoutController@disburse');
        $router->post('/mlm-payouts/cancel', 'Admin\PayoutController@cancel');

        $router->get('/mlm-engagement', 'Admin\EngagementController@index');
        $router->get('/mlm-engagement/metrics', 'Admin\EngagementController@metrics');
        $router->get('/mlm-engagement/leaderboard', 'Admin\EngagementController@leaderboard');
        $router->get('/mlm-engagement/goals', 'Admin\EngagementController@goals');
        $router->get('/mlm-engagement/goal-details', 'Admin\EngagementController@goalDetails');
        $router->get('/mlm-engagement/notifications', 'Admin\EngagementController@notificationFeed');
        $router->post('/mlm-engagement/goals/create', 'Admin\EngagementController@createGoal');
        $router->post('/mlm-engagement/goals/update', 'Admin\EngagementController@updateGoal');
        $router->post('/mlm-engagement/goals/progress', 'Admin\EngagementController@recordGoalProgress');
        $router->post('/mlm-engagement/goals/status', 'Admin\EngagementController@updateGoalStatus');
        $router->post('/mlm-engagement/notifications/mark-read', 'Admin\EngagementController@markNotificationRead');
        $router->post('/mlm-engagement/notifications/mark-all-read', 'Admin\EngagementController@markAllNotificationsRead');
    });

    // Associate routes - Using routes/web.php for now as they are complete
    /*
    $router->group(['prefix' => 'associate', 'middleware' => ['auth', 'associate']], function ($router) {
        $router->get('/', 'Associate\DashboardController@index');
        $router->get('/leads', 'Associate\LeadController@index');
        $router->get('/commissions', 'Associate\CommissionController@index');
        $router->get('/referrals', 'Associate\ReferralController@index');
    });
    */

    // Customer Portal Routes (Restored)
    $router->group(['prefix' => 'customer'], function ($router) {
        $router->get('/login', 'Customer\CustomerController@login');
        $router->post('/authenticate', 'Customer\CustomerController@authenticate');
        $router->get('/register', 'Customer\CustomerController@register');
        $router->post('/register', 'Customer\CustomerController@processRegistration');

        // Authenticated Customer Routes
        $router->group(['middleware' => 'auth'], function ($router) {
            $router->get('/dashboard', 'Customer\CustomerController@dashboard');
            $router->get('/emi-calculator', 'Customer\CustomerController@emiCalculator');
            $router->post('/emi-calculate', 'Customer\CustomerController@calculateEMI');
            $router->post('/emi-save', 'Customer\CustomerController@saveEMICalculation');
            $router->get('/emi-history', 'Customer\CustomerController@emiHistory');
        });
    });

    // SaaS Professional Dashboard Routes (Restored)
    $router->group(['prefix' => 'professional', 'middleware' => ['auth']], function ($router) {
        $router->get('/dashboard', 'SaaS\ProfessionalDashboardController@index');
        $router->get('/projects/inventory', 'SaaS\ProfessionalToolsController@inventory');
        $router->get('/construction/workflow', 'SaaS\ProfessionalToolsController@workflow');
        $router->get('/expenses/manage', 'SaaS\ProfessionalToolsController@expenses');
        $router->get('/labor/management', 'SaaS\ProfessionalToolsController@labor');
        $router->get('/marketing/whatsapp', 'SaaS\ProfessionalToolsController@whatsapp');
        $router->get('/referrals', 'SaaS\ProfessionalToolsController@referrals');
        $router->get('/documents', 'SaaS\ProfessionalToolsController@documents');
    });
});

// Legacy route compatibility layer
// This ensures that any routes defined in the legacy format still work
if (file_exists(__DIR__ . '/web.php')) {
    // Legacy routes will be loaded after modern routes
    // and will be handled by the fallback mechanism in the modern router
}
