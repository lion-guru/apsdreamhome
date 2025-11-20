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
Route::get('/admin/mlm', 'AdminController@mlmDashboard');
Route::get('/admin/commissions', 'AdminController@commissions');
Route::get('/admin/network', 'AdminController@network');
Route::get('/admin/mlm/analytics', 'AdminAnalyticsController@index');
Route::get('/admin/mlm/analytics/data', 'AdminAnalyticsController@data');
Route::get('/admin/mlm/analytics/ledger', 'AdminAnalyticsController@ledger');
Route::get('/admin/mlm/analytics/export', 'AdminAnalyticsController@export');
Route::get('/admin/network/inspector', 'AdminNetworkController@index');
Route::get('/admin/network/search', 'AdminNetworkController@searchUsers');
Route::get('/admin/network/tree', 'AdminNetworkController@networkTree');
Route::get('/admin/network/agreements', 'AdminNetworkController@listAgreements');
Route::post('/admin/network/agreements/create', 'AdminNetworkController@createAgreement');
Route::post('/admin/network/agreements/update', 'AdminNetworkController@updateAgreement');
Route::post('/admin/network/agreements/delete', 'AdminNetworkController@deleteAgreement');
Route::post('/admin/network/rebuild', 'AdminNetworkController@rebuildNetwork');
Route::get('/admin/payouts', 'AdminPayoutController@index');
Route::get('/admin/payouts/list', 'AdminPayoutController@list');
Route::get('/admin/payouts/export', 'AdminPayoutController@export');
Route::post('/admin/payouts/create', 'AdminPayoutController@create');
Route::post('/admin/payouts/approve', 'AdminPayoutController@approve');
Route::post('/admin/payouts/disburse', 'AdminPayoutController@disburse');
Route::post('/admin/payouts/cancel', 'AdminPayoutController@cancel');
Route::get('/admin/payouts/items', 'AdminPayoutController@items');
Route::get('/admin/engagement/metrics', 'AdminEngagementController@metrics');
Route::get('/admin/engagement/leaderboard', 'AdminEngagementController@leaderboard');
Route::get('/admin/engagement/goals', 'AdminEngagementController@goals');
Route::get('/admin/engagement/goal-details', 'AdminEngagementController@goalDetails');
Route::get('/admin/engagement/notifications', 'AdminEngagementController@notificationFeed');
Route::get('/admin/engagement/preferences', 'AdminEngagementController@notificationPreferences');
Route::post('/admin/engagement/goals/create', 'AdminEngagementController@createGoal');
Route::post('/admin/engagement/goals/update', 'AdminEngagementController@updateGoal');
Route::post('/admin/engagement/goals/progress', 'AdminEngagementController@recordGoalProgress');
Route::post('/admin/engagement/goals/status', 'AdminEngagementController@updateGoalStatus');
Route::post('/admin/engagement/notifications/mark-read', 'AdminEngagementController@markNotificationRead');
Route::post('/admin/engagement/notifications/mark-all-read', 'AdminEngagementController@markAllNotificationsRead');
?>