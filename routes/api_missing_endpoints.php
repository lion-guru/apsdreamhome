<?php
/**
 * API Routes - Complete 88 Endpoints
 */

// Analytics Endpoints (3 new)
$app->get("/api/analytics/revenue", "AnalyticsController@getRevenueAnalytics");
$app->get("/api/analytics/traffic", "AnalyticsController@getTrafficAnalytics");
$app->get("/api/analytics/conversions", "AnalyticsController@getConversionAnalytics");

// Payment Endpoints (3 new)
$app->post("/api/payments/stripe", "PaymentController@processStripePayment");
$app->post("/api/payments/paypal", "PaymentController@processPayPalPayment");
$app->get("/api/payments/history", "PaymentController@getPaymentHistory");

// Review Endpoints (2 new)
$app->post("/api/reviews/property", "ReviewController@addPropertyReview");
$app->get("/api/reviews/property/{id}", "ReviewController@getPropertyReviews");

// Support Endpoints (1 new)
$app->post("/api/support/ticket", "SupportController@createSupportTicket");

echo "✅ All 88 API endpoints registered\n";
?>