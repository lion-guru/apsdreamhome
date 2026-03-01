# APS Dream Home - Production Deployment Checklist
# ================================================

## 🚀 Pre-Deployment Checklist

### 🔒 Security Setup
- [ ] Generate unique APP_KEY: `php artisan key:generate`
- [ ] Update all database passwords with strong passwords
- [ ] Configure SSL certificates (Let's Encrypt recommended)
- [ ] Set up firewall rules
- [ ] Disable debug mode: `APP_DEBUG=false`
- [ ] Enable HTTPS redirects
- [ ] Configure security headers
- [ ] Set up file upload restrictions

### 🗄️ Database Setup
- [ ] Create production database
- [ ] Run database migrations
- [ ] Import production data (if any)
- [ ] Set up database user with limited privileges
- [ ] Configure database backups
- [ ] Test database connections
- [ ] Run performance indexes migration

### 📧 Email Configuration
- [ ] Configure SMTP settings
- [ ] Test email sending functionality
- [ ] Set up email templates
- [ ] Configure bounce handling
- [ ] Test notification system

### 💳 Payment Gateway Setup
- [ ] Configure Razorpay credentials
- [ ] Set up webhook endpoints
- [ ] Test payment flow
- [ ] Configure refund handling
- [ ] Set up transaction logging

### 🤖 AI Services Setup
- [ ] Configure Gemini API key
- [ ] Set up OpenRouter API
- [ ] Test AI chat functionality
- [ ] Configure rate limits for AI services
- [ ] Set up fallback mechanisms

### 🗂️ File Storage Setup
- [ ] Configure upload directories
- [ ] Set proper permissions (755 for directories, 644 for files)
- [ ] Configure CDN if using
- [ ] Set up file backup strategy
- [ ] Test file upload functionality

### 🔧 Server Configuration
- [ ] Set up web server (Apache/Nginx)
- [ ] Configure PHP settings (memory_limit, max_execution_time)
- [ ] Enable OPcache for performance
- [ ] Set up cron jobs for scheduled tasks
- [ ] Configure log rotation
- [ ] Set up monitoring

### 📊 Monitoring & Logging
- [ ] Set up application monitoring
- [ ] Configure error reporting (Sentry recommended)
- [ ] Set up performance monitoring
- [ ] Configure log aggregation
- [ ] Set up uptime monitoring
- [ ] Create alerting rules

### 🧪 Testing
- [ ] Run full application test suite
- [ ] Test all user workflows
- [ ] Test payment processing
- [ ] Test email notifications
- [ ] Test file uploads
- [ ] Test API endpoints
- [ ] Perform security scan
- [ ] Load testing

## 🚀 Deployment Steps

### 1. Environment Setup
```bash
# Copy production environment file
cp .env.production .env

# Update with actual values
nano .env

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

### 2. Database Migration
```bash
# Run migrations
php artisan migrate --force

# Run database seeder (if needed)
php artisan db:seed --force

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. File Permissions
```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 644 storage/logs/*
```

### 4. Cache Clearing
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 5. Queue Setup (if using)
```bash
# Start queue worker
php artisan queue:work --daemon
```

## 📋 Post-Deployment Checklist

### 🔍 Verification
- [ ] Test all major user flows
- [ ] Verify SSL certificate is working
- [ ] Check all forms are submitting correctly
- [ ] Test login/logout functionality
- [ ] Verify email sending
- [ ] Test payment processing
- [ ] Check file uploads
- [ ] Test search functionality

### 📊 Performance Check
- [ ] Monitor page load times
- [ ] Check database query performance
- [ ] Verify caching is working
- [ ] Test under load
- [ ] Check memory usage

### 🔒 Security Verification
- [ ] Run security scan
- [ ] Check for exposed credentials
- [ ] Verify HTTPS is enforced
- [ ] Test security headers
- [ ] Check file upload security
- [ ] Verify rate limiting

### 📈 Monitoring Setup
- [ ] Verify error logging
- [ ] Check performance metrics
- [ ] Set up alerts
- [ ] Test backup systems
- [ ] Verify uptime monitoring

## 🚨 Emergency Rollback Plan

### Quick Rollback
```bash
# Restore previous version
git checkout [previous-commit-tag]

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

### Database Rollback
```bash
# Rollback migrations if needed
php artisan migrate:rollback --step=1
```

## 📞 Emergency Contacts

- **System Administrator**: [Contact Info]
- **Database Administrator**: [Contact Info]
- **Security Team**: [Contact Info]
- **Hosting Provider**: [Contact Info]

## 🔄 Maintenance Schedule

### Daily
- [ ] Check error logs
- [ ] Monitor system performance
- [ ] Verify backups completed

### Weekly
- [ ] Security scan
- [ ] Performance review
- [ ] Update dependencies check

### Monthly
- [ ] Security updates
- [ ] Dependency updates
- [ ] Backup verification

## 📚 Documentation Links

- [API Documentation](/api-docs)
- [Admin Guide](/docs/admin)
- [User Manual](/docs/user)
- [Troubleshooting Guide](/docs/troubleshooting)

---

**Remember**: Always test in a staging environment before deploying to production!
