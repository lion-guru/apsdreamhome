# APS Dream Home - Co-Worker Setup Instructions

## 🚀 QUICK SETUP GUIDE

### 📋 REQUIREMENTS:
- XAMPP installed (Apache + MySQL)
- PHP 8.0+ with required extensions
- Admin access to system

### 🛠️ SETUP STEPS:

#### STEP 1: EXTRACT DEPLOYMENT PACKAGE
1. Extract `apsdreamhome_deployment_package.zip`
2. Copy to: `C:\xampp\htdocs\apsdreamhome\`

#### STEP 2: DATABASE SETUP
1. Start XAMPP MySQL service
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create database: `apsdreamhome`
4. Import: `apsdreamhome_database.sql`

#### STEP 3: CONFIGURATION
1. Edit `config/database.php` with your credentials
2. Set file permissions for `uploads/`, `logs/`, `cache/`
3. Enable GD extension in `php.ini`

#### STEP 4: VERIFICATION
1. Open: http://localhost/apsdreamhome/verify_deployment.php
2. Review test results
3. Fix any issues using DEPLOYMENT_FIX_GUIDE.md

#### STEP 5: FINAL STEPS
1. Test main application: http://localhost/apsdreamhome/
2. Create admin user account
3. Configure basic settings
4. Report success to admin

## 🔧 TROUBLESHOOTING:
- GD Extension: See DEPLOYMENT_FIX_GUIDE.md
- Database Issues: Check MySQL service
- Permission Issues: Set folder permissions
- 404 Errors: Check .htaccess configuration

## 📞 SUPPORT:
- Contact admin for assistance
- Review documentation
- Check error logs

## ✅ SUCCESS CRITERIA:
- All verification tests pass (95%+)
- Application loads correctly
- Database connectivity working
- User accounts functional
