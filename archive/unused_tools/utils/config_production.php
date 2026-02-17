<?php
/**
 * APS Dream Homes - Production Configuration
 * Generated: 2025-11-29 08:21:52
 */

// Production environment
define('ENVIRONMENT', 'production');

// Security settings
define('SECURITY_KEY', '89df3a4fd561238aef14af5ca16f565cec5fc082ce6801e4c09e902306b4a10d');
define('JWT_SECRET', '5eda3acd465f419d1b7f0545fc74b94a10129a8fe25e0281c713771b51365cc8');

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour

// API settings
define('API_RATE_LIMIT', 100);
define('API_RATE_WINDOW', 3600);

// Email settings (configure these)
define('SMTP_HOST', 'your_smtp_host');
define('SMTP_USER', 'your_smtp_user');
define('SMTP_PASS', 'your_smtp_pass');
define('SMTP_PORT', 587);

// Payment settings (configure these)
define('RAZORPAY_KEY', 'your_razorpay_key');
define('RAZORPAY_SECRET', 'your_razorpay_secret');
define('STRIPE_PUBLISHABLE', 'your_stripe_key');
define('STRIPE_SECRET', 'your_stripe_secret');

// Backup settings
define('BACKUP_ENABLED', true);
define('BACKUP_SCHEDULE', 'daily');
define('BACKUP_RETENTION', 30); // days

// Monitoring settings
define('MONITORING_ENABLED', true);
define('ERROR_LOGGING', true);
define('PERFORMANCE_LOGGING', true);
