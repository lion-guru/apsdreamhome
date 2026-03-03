# APS Dream Home - Deployment Fix Guide

## 🚨 COMMON ISSUES & FIXES

### 🖼️ GD EXTENSION ISSUE (MOST COMMON)
**Problem**: GD extension not installed/enabled
**Symptoms**: Image upload failures, captcha not working

#### FIX FOR XAMPP:
1. Open: `C:\xampp\php\php.ini`
2. Find: `;extension=gd`
3. Remove semicolon: `extension=gd`
4. Save file
5. Restart Apache service
6. Verify: Create test.php with `<?php phpinfo(); ?>`

#### ALTERNATIVE FIX:
1. Download GD extension DLL
2. Place in: `C:\xampp\php\ext\`
3. Add to php.ini: `extension=gd`
4. Restart Apache

### 🗄️ DATABASE CONNECTION ISSUES
**Problem**: Cannot connect to database
**Symptoms**: Connection errors, blank pages

#### FIXES:
1. Check MySQL service is running
2. Verify database exists: `apsdreamhome`
3. Check credentials in `config/database.php`
4. Test with phpMyAdmin
5. Import database if missing

### 📁 FILE PERMISSION ISSUES
**Problem**: Cannot write to directories
**Symptoms**: Upload failures, log errors

#### FIXES:
1. Set permissions for:
   - `uploads/` (writable)
   - `logs/` (writable)
   - `cache/` (writable)
2. Windows: Right-click > Properties > Security > Edit permissions
3. XAMPP: Use XAMPP shell for chmod commands

### 🌐 URL REWRITING ISSUES
**Problem**: 404 errors, pretty URLs not working
**Symptoms**: Pages not found, routing issues

#### FIXES:
1. Check `.htaccess` file exists in `public/`
2. Enable Apache mod_rewrite:
   - Open: `C:\xampp\apache\conf\httpd.conf`
   - Find: `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Remove `#`: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Restart Apache

### 🔧 CONFIGURATION ISSUES
**Problem**: Configuration errors
**Symptoms**: White screens, error messages

#### FIXES:
1. Check `config/paths.php` for correct paths
2. Verify BASE_URL is correct
3. Check file permissions
4. Enable error reporting for debugging

## 🧪 VERIFICATION PROCESS:
1. Run: `http://localhost/apsdreamhome/verify_deployment.php`
2. Review all test results
3. Apply fixes for failed tests
4. Re-run verification
5. Report final results

## 📞 GETTING HELP:
1. Check error logs: `logs/error.log`
2. Review this guide first
3. Contact admin with specific error details
4. Include verification results in report

## ✅ SUCCESS INDICATORS:
- All verification tests pass (95%+)
- No error messages in logs
- Application loads correctly
- All features working as expected
