# APS Dream Homes MLM System - Production Deployment Guide

## 🚀 Quick Start (5 Minutes)

### 1. Deploy the Complete System
```bash
# From project root
cd c:/xampp/htdocs/apsdreamhome

# Deploy everything
php deploy_mlm_system.php deploy

# Test the system
php tests/test_mlm_system.php
```

### 2. Access Your MLM System
- **Registration**: http://localhost/apsdreamhome/register
- **Network Dashboard**: http://localhost/apsdreamhome/dashboard
- **Admin Panel**: http://localhost/apsdreamhome/admin/

## 📋 Production Checklist

### ✅ Pre-Deployment
- [ ] Database backup created
- [ ] Legacy files archived
- [ ] All tests passed
- [ ] Configuration validated

### ✅ Post-Deployment
- [ ] Registration flow tested
- [ ] Referral codes working
- [ ] Commission calculations verified
- [ ] Network tree building correctly
- [ ] Dashboard analytics displaying

## 🔧 System Architecture

### Database Schema
```sql
-- Core MLM Tables
- mlm_profiles (user referral profiles)
- mlm_network_tree (multi-level relationships)
- mlm_referrals (referral tracking)
- mlm_commission_ledger (commission records)
- mlm_payout_batches (batch payouts)
```

### Commission Structure
```
Level 1: 5% (Direct sponsor)
Level 2: 3% 
Level 3: 2%
Level 4: 1.5%
Level 5: 1%
```

## 🎯 User Journey Flows

### 1. New User Registration
```
Visitor → Register Page → Select Role → Enter Details → Submit
                    ↓
              Auto-generate Referral Code
                    ↓
              Build Network Tree
                    ↓
              Redirect to Dashboard
```

### 2. Referral Process
```
User → Get Referral Link → Share Link → New User Registers
                    ↓
              Track Referral → Calculate Commission
                    ↓
              Update Network Tree
```

### 3. Commission Flow
```
Property Sale → Calculate 5-level Commission → Create Records
                    ↓
              Admin Approval → Process Payout
                    ↓
              Update User Balances
```

## 📊 Key URLs & Features

### Public URLs
- `http://localhost/apsdreamhome/register` - Unified registration
- `http://localhost/apsdreamhome/login` - User login
- `http://localhost/apsdreamhome/dashboard` - Network dashboard

### Admin URLs
- `http://localhost/apsdreamhome/admin/` - Admin login
- `http://localhost/apsdreamhome/admin/mlm` - MLM management
- `http://localhost/apsdreamhome/admin/commissions` - Commission approval

### API Endpoints
- `GET /api/network/tree` - Network tree data
- `GET /api/network/analytics` - Referral analytics
- `POST /commissions/calculate` - Calculate commissions

## 🔍 Testing Commands

### Run All Tests
```bash
php tests/test_mlm_system.php
```

### Test Specific Scenarios
```bash
# Test registration flow
php -r "include 'tests/test_mlm_system.php'; (new MLMTestingSuite)->testRegistrationFlow();"

# Test commission calculation
php -r "include 'tests/test_mlm_system.php'; (new MLMTestingSuite)->testCommissionCalculation();"
```

## 🛠️ Configuration

### Database Settings
```php
// In includes/config.php
MLM_ENABLED = true;
MLM_MAX_LEVELS = 5;
MLM_COMMISSION_STRUCTURE = [5, 3, 2, 1.5, 1]; // percentages
```

### Email Templates
- Registration confirmation
- Referral notifications
- Commission earned alerts
- Payout confirmations

## 📈 Monitoring & Analytics

### Key Metrics to Track
- Daily registrations
- Referral conversion rate
- Commission payouts
- Network growth rate
- User engagement

### Admin Dashboard Features
- Real-time network visualization
- Commission approval queue
- Payout batch management
- User activity tracking

## 🚨 Troubleshooting

### Common Issues & Solutions

#### Issue: Registration not working
```bash
# Check database connection
mysql -u root -e "USE apsdreamhome; SHOW TABLES LIKE 'mlm%';"

# Verify schema
mysql -u root -e "DESCRIBE mlm_profiles;"
```

#### Issue: Commission not calculating
```bash
# Check network tree
mysql -u root -e "SELECT COUNT(*) FROM mlm_network_tree;"

# Verify commission ledger
mysql -u root -e "SELECT * FROM mlm_commission_ledger LIMIT 5;"
```

#### Issue: Referral links not working
```bash
# Check referral codes
mysql -u root -e "SELECT referral_code FROM mlm_profiles LIMIT 5;"

# Test API endpoint
curl http://localhost/apsdreamhome/api/network/validate-code?code=TEST123
```

## 🔐 Security Features

### Data Protection
- SQL injection prevention (prepared statements)
- XSS protection (output escaping)
- CSRF tokens on forms
- Password hashing (bcrypt)
- Session management

### Access Control
- Role-based permissions
- Admin approval required
- Audit logging
- IP tracking

## 📱 Mobile Optimization

### Responsive Design
- Bootstrap 5 mobile-first
- Touch-friendly interfaces
- Optimized images
- Fast loading times

### Progressive Web App
- Service worker ready
- Offline capability
- Push notifications
- App-like experience

## 🚀 Performance Optimization

### Database Optimization
- Indexed foreign keys
- Optimized queries
- Connection pooling
- Caching strategies

### Frontend Optimization
- Minified CSS/JS
- Lazy loading
- CDN integration
- Image optimization

## 📞 Support & Maintenance

### Regular Tasks
- Weekly commission processing
- Monthly payout batches
- User data cleanup
- Performance monitoring

### Backup Strategy
- Daily database backups
- Weekly file backups
- Monthly full system backup
- Cloud storage integration

## ✅ Post-Deployment Checklist

**Backups & Rollback**
- Backup folder: `backups/2025-11-12_20-56-19/`
- Rollback command: `php deploy_mlm_system.php rollback`
- Deployment log: `deployment.log`

**Manual Verification**
1. Visit `/register` → complete a test signup (use referral code if available).
2. Verify `/dashboard` shows referral link, stats, and network data.
3. Log into `/admin/` → confirm MLM widgets, commissions, and routes are available.
4. Check `mlm_commission_ledger` for migrated records and new entries.

**Monitoring & Alerts**
- Set up alert if tests fail (`php tests/test_mlm_system.php`).
- Track newly created referral codes and network tree entries.
- Monitor pending vs. approved commission totals.

**Phase 2 Planning**
- Sponsor data import (if legacy sources exist).
- Commission reporting dashboards.
- Automated payout scheduling.
- Enhanced notifications (email/WhatsApp) for referrals and payouts.

## 🎯 Next Steps After Deployment

### Week 1: Monitoring
- [ ] Monitor registration rates
- [ ] Track referral success
- [ ] Verify commission calculations
- [ ] Check system performance

### Week 2: Optimization
- [ ] Optimize slow queries
- [ ] Improve user experience
- [ ] Add missing features
- [ ] Fix any bugs

### Week 3: Scaling
- [ ] Add more user types
- [ ] Enhance commission rules
- [ ] Integrate payment gateways
- [ ] Add reporting features

## 📞 Contact & Support

For technical support or questions:
- **Email**: support@apsdreamhomes.com
- **Phone**: +91-1234567890
- **Documentation**: See MLM_IMPLEMENTATION_WORKFLOW.md
- **Testing**: Use tests/test_mlm_system.php

---

**🎉 Your MLM referral system is now production-ready!**

**Quick Access:**
- **Register**: http://localhost/apsdreamhome/register
- **Dashboard**: http://localhost/apsdreamhome/dashboard
- **Admin**: http://localhost/apsdreamhome/admin/
