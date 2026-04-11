# 🌐 Domain Migration Guide - Localhost to Live Domain

## ✅ **GOOD NEWS: Most Things Auto-Detect!**

Your APS Dream Home project now **auto-detects** the domain automatically! No code changes needed in most cases.

---

## 🚀 **Files That Auto-Detect Domain (No Changes Needed):**

### ✅ `config/path_manager.php`
- **Status:** ✅ Auto-detects domain dynamically
- **Works on:** localhost, apsdreamhome.com, any domain
- **How:** Uses `$_SERVER['HTTP_HOST']` to detect current domain

### ✅ `config/bootstrap.php`  
- **Status:** ✅ Auto-detects BASE_URL
- **How:** Dynamic detection based on server variables

### ✅ `public/index.php`
- **Status:** ✅ Auto-detects environment
- **How:** Checks `$_SERVER['HTTP_HOST']` for domain

---

## ⚠️ **Files to Update When Migrating:**

### 1. **Database Configuration** (If using different DB on live)
```php
// config/environments/production.php (Create this file)
<?php
return [
    'DB_HOST' => 'localhost',  // Or your live DB host
    'DB_NAME' => 'apsdreamhome',
    'DB_USER' => 'your_live_db_user',  // NOT root
    'DB_PASS' => 'your_strong_password',
    'APP_URL' => 'https://www.apsdreamhome.com',
];
```

### 2. **Google OAuth (If using Google Login)**
```php
// config/google_oauth_config.php
// Update redirect URLs:
$host = $_SERVER['HTTP_HOST'];
if ($host === 'localhost' || $host === '127.0.0.1') {
    $redirectUri = 'http://localhost/apsdreamhome/google_callback.php';
} else {
    $redirectUri = 'https://www.apsdreamhome.com/google_callback.php';
}
```

### 3. **Email/SMTP Configuration**
```php
// config/environments/production.php
'SMTP_HOST' => 'smtp.gmail.com',  // Your email provider
'SMTP_USER' => 'noreply@apsdreamhome.com',
'SMTP_PASS' => 'your_app_password',
```

### 4. **SSL/HTTPS (IMPORTANT!)**
Add to `.htaccess` in public folder:
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📋 **Migration Checklist:**

### **Phase 1: Upload Files**
- [ ] Upload all files to live server (public_html/)
- [ ] Upload database (export from XAMPP, import to live MySQL)
- [ ] Verify file permissions (755 for folders, 644 for files)

### **Phase 2: Database**
- [ ] Create live MySQL database
- [ ] Import database from localhost
- [ ] Update DB credentials in config
- [ ] Test database connection

### **Phase 3: Configuration**
- [ ] Update `config/environments/production.php`
- [ ] Set environment to 'production'
- [ ] Disable debug mode
- [ ] Configure SSL/HTTPS

### **Phase 4: Testing**
- [ ] Visit: https://www.apsdreamhome.com
- [ ] Test homepage loads
- [ ] Test login/register
- [ ] Test chatbot working
- [ ] Test property pages
- [ ] Test admin panel

### **Phase 5: Final Touches**
- [ ] Update Google OAuth redirect URLs (if using)
- [ ] Configure SSL certificate
- [ ] Set up CDN for images (optional)
- [ ] Configure backup system

---

## 🔧 **Quick Config Update Script:**

Create `config/set_production.php`:
```php
<?php
// Run this once after uploading to live server

$envFile = __DIR__ . '/environments/production.php';

$config = <<<'CONFIG'
<?php
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'apsdreamhome',
    'DB_USER' => 'CHANGE_THIS',
    'DB_PASS' => 'CHANGE_THIS',
    'APP_URL' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    'APP_ENV' => 'production',
    'APP_DEBUG' => false,
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => 587,
    'SMTP_USER' => 'CHANGE_THIS',
    'SMTP_PASS' => 'CHANGE_THIS',
    'CSRF_PROTECTION' => true,
    'SESSION_TIMEOUT' => 3600,
];
CONFIG;

file_put_contents($envFile, $config);
echo "✅ Production config created!\n";
echo "📝 Now edit: config/environments/production.php\n";
echo "   Update DB_USER, DB_PASS, SMTP credentials\n";
```

---

## 🌍 **Domain Scenarios - All Work Automatically:**

### Scenario 1: Localhost (Current)
- URL: `http://localhost/apsdreamhome`
- ✅ Works automatically

### Scenario 2: Localhost with Port
- URL: `http://localhost:8080/apsdreamhome`  
- ✅ Works automatically

### Scenario 3: IP Address
- URL: `http://192.168.1.100/apsdreamhome`
- ✅ Works automatically

### Scenario 4: Subdomain
- URL: `https://aps.yourdomain.com`
- ✅ Works automatically

### Scenario 5: Main Domain
- URL: `https://www.apsdreamhome.com`
- ✅ Works automatically

### Scenario 6: Subfolder on Live
- URL: `https://yourdomain.com/apsdreamhome`
- ✅ Works automatically

---

## 🛡️ **Security Changes for Production:**

### 1. **Disable Error Display**
```php
// public/index.php
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 'Off');
}
```

### 2. **Enable HTTPS Only**
```php
// public/index.php - Add at top
if (getenv('APP_ENV') === 'production' && !isset($_SERVER['HTTPS'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### 3. **Secure Session Cookies**
```php
// config/bootstrap.php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');  // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
```

### 4. **Database Security**
```php
// Create separate DB user (NOT root)
CREATE USER 'aps_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📝 **Summary - What to Change:**

| File | Change Required? | Notes |
|------|-----------------|-------|
| `config/path_manager.php` | ❌ No | Auto-detects now |
| `config/bootstrap.php` | ❌ No | Auto-detects now |
| `public/index.php` | ❌ No | Auto-detects now |
| `config/environments/production.php` | ✅ Yes | Create this file |
| Database credentials | ✅ Yes | Update for live DB |
| Google OAuth | ⚠️ Maybe | Update redirect URLs |
| SMTP/Email | ✅ Yes | Configure live email |
| `.htaccess` | ✅ Yes | Add HTTPS redirect |
| SSL Certificate | ✅ Yes | Install on server |

---

## 🎯 **One-Line Domain Migration:**

After uploading to live server, just update database credentials in:
```
config/environments/production.php
```

Everything else works automatically! 🎉

---

## 📞 **Need Help?**

Common issues and solutions:

### Issue: "Database connection failed"
**Solution:** Update DB_HOST, DB_USER, DB_PASS in production config

### Issue: "CSS/JS not loading"
**Solution:** Clear browser cache, check BASE_URL is detecting correctly

### Issue: "404 errors on routes"
**Solution:** Check .htaccess mod_rewrite is enabled

### Issue: "Session not working"
**Solution:** Check session path is writable on live server

---

**🚀 Ready for Domain Migration!**
