<?php
// MLM System Routes

// Authentication
Route::get('/auth/register', 'AuthController@register');
Route::post('/auth/register', 'AuthController@processRegistration');
Route::get('/auth/login', 'AuthController@login');
Route::post('/auth/login', 'AuthController@processLogin');
Route::get('/auth/logout', 'AuthController@logout');

// Legacy redirects for backward compatibility
Route::get('/register', 'AuthController@register');
Route::get('/login', 'AuthController@login');
Route::get('/logout', 'AuthController@logout');

// Test routes for view consolidation
Route::get('/test/consolidation', 'TestController@consolidation');
Route::get('/test/layout', 'TestController@layout');

// Network Dashboard
Route::get('/dashboard', 'NetworkController@dashboard');
Route::get('/api/network/tree', 'NetworkController@getNetworkTree');
Route::get('/api/network/analytics', 'NetworkController@getAnalytics');
Route::get('/api/network/referral-link', 'NetworkController@getReferralLink');
Route::get('/api/network/validate-code', 'NetworkController@validateCode');

// Commission Management
Route::get('/commissions', 'CommissionController@index');
Route::post('/commissions/calculate', 'CommissionController@calculate');
Route::post('/commissions/approve', 'CommissionController@approve');
Route::get('/commissions/payout', 'CommissionController@processPayout');

// Admin Routes
Route::get('/admin', 'AdminController@index');
Route::get('/admin/dashboard', 'AdminController@index');
Route::get('/admin/users', 'AdminController@users');
Route::get('/admin/properties', 'AdminController@properties');
Route::get('/admin/properties/create', 'AdminController@createProperty');
Route::get('/admin/associates', 'AdminController@associates');
// Customer Management
Route::get('/admin/customers/search', 'Admin\CustomerController@search');
Route::get('/admin/customers', 'AdminController@customers');
Route::get('/admin/bookings', 'AdminController@bookings');
Route::get('/admin/employees', 'AdminController@employees');
Route::get('/admin/settings', 'AdminController@settings');

// Payment Management
Route::get('/admin/payments', 'Admin\PaymentController@index');
Route::get('/admin/payments/dashboard-stats', 'Admin\PaymentController@dashboardStats');
Route::get('/admin/payments/data', 'Admin\PaymentController@data');
Route::get('/admin/payments/customers', 'Admin\PaymentController@customers');
Route::post('/admin/payments/store', 'Admin\PaymentController@store');
Route::post('/admin/payments/update/{id}', 'Admin\PaymentController@update');
Route::post('/admin/payments/delete/{id}', 'Admin\PaymentController@destroy');
Route::get('/admin/payments/show/{id}', 'Admin\PaymentController@show');
Route::get('/admin/payments/receipt/{id}', 'Admin\PaymentController@receipt');
Route::get('/admin/payments/edit/{id}', 'Admin\PaymentController@edit');

// CRM / Lead Management
Route::get('/admin/leads', 'CRM\LeadController@index');
Route::get('/admin/leads/create', 'CRM\LeadController@create');
Route::post('/admin/leads/store', 'CRM\LeadController@store');
Route::get('/admin/leads/edit/{id}', 'CRM\LeadController@edit');
Route::post('/admin/leads/update/{id}', 'CRM\LeadController@update');
Route::post('/admin/leads/delete/{id}', 'CRM\LeadController@destroy');
Route::get('/admin/leads/{id}', 'CRM\LeadController@show');
Route::post('/admin/leads/{id}/activity', 'CRM\LeadController@addActivity');
Route::post('/admin/leads/{id}/note', 'CRM\LeadController@addNote');

// Visit Management
Route::get('/admin/visits', 'Admin\VisitController@index');
Route::get('/admin/visits/create', 'Admin\VisitController@create');
Route::post('/admin/visits/store', 'Admin\VisitController@store');
Route::post('/admin/visits/update-status/{id}', 'Admin\VisitController@updateStatus');

// EMI Management
Route::get('/admin/emi', 'Admin\EMIController@index');
Route::post('/admin/emi/list', 'Admin\EMIController@list');
Route::get('/admin/emi/stats', 'Admin\EMIController@stats');
Route::get('/admin/emi/show/{id}', 'Admin\EMIController@show');
Route::post('/admin/emi/store', 'Admin\EMIController@store');
Route::post('/admin/emi/pay', 'Admin\EMIController@pay');

// MLM Admin Routes
Route::get('/admin/mlm', 'AdminController@mlmDashboard');
Route::get('/admin/commissions', 'AdminController@commissions');
Route::get('/admin/network', 'AdminController@network');
Route::get('/admin/mlm/analytics', 'Admin\AnalyticsController@index');
Route::get('/admin/mlm/analytics/data', 'Admin\AnalyticsController@data');
Route::get('/admin/mlm/analytics/ledger', 'Admin\AnalyticsController@ledger');
Route::get('/admin/mlm/analytics/export', 'Admin\AnalyticsController@export');
Route::get('/admin/network/inspector', 'Admin\NetworkController@index');
Route::get('/admin/network/search', 'Admin\NetworkController@searchUsers');
Route::get('/admin/network/tree', 'Admin\NetworkController@networkTree');
Route::get('/admin/network/agreements', 'Admin\NetworkController@listAgreements');
Route::post('/admin/network/agreements/create', 'Admin\NetworkController@createAgreement');
Route::post('/admin/network/agreements/update', 'Admin\NetworkController@updateAgreement');
Route::post('/admin/network/agreements/delete', 'Admin\NetworkController@deleteAgreement');
Route::post('/admin/network/rebuild', 'Admin\NetworkController@rebuildNetwork');
Route::get('/admin/payouts', 'Admin\PayoutController@index');
Route::get('/admin/payouts/list', 'Admin\PayoutController@list');
Route::get('/admin/payouts/export', 'Admin\PayoutController@export');
Route::post('/admin/payouts/create', 'Admin\PayoutController@create');
Route::post('/admin/payouts/approve', 'Admin\PayoutController@approve');
Route::post('/admin/payouts/disburse', 'Admin\PayoutController@disburse');
Route::post('/admin/payouts/cancel', 'Admin\PayoutController@cancel');
Route::get('/admin/payouts/items', 'Admin\PayoutController@items');
Route::get('/admin/engagement/metrics', 'Admin\EngagementController@metrics');
Route::get('/admin/engagement/leaderboard', 'Admin\EngagementController@leaderboard');
Route::get('/admin/engagement/goals', 'Admin\EngagementController@goals');
Route::get('/admin/engagement/goal-details', 'Admin\EngagementController@goalDetails');
Route::get('/admin/engagement/notifications', 'Admin\EngagementController@notificationFeed');
Route::get('/admin/engagement/preferences', 'Admin\EngagementController@notificationPreferences');
Route::post('/admin/engagement/goals/create', 'Admin\EngagementController@createGoal');
Route::post('/admin/engagement/goals/update', 'Admin\EngagementController@updateGoal');
Route::post('/admin/engagement/goals/progress', 'Admin\EngagementController@recordGoalProgress');
Route::post('/admin/engagement/goals/status', 'Admin\EngagementController@updateGoalStatus');
Route::post('/admin/engagement/notifications/mark-read', 'Admin\EngagementController@markNotificationRead');
Route::post('/admin/engagement/notifications/mark-all-read', 'Admin\EngagementController@markAllNotificationsRead');
