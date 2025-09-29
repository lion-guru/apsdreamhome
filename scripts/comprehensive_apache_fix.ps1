# Comprehensive Apache Configuration Fix
# Run this script with Administrator privileges

# Stop Apache Service
net stop apache2

# Backup existing configuration files
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.bak_$timestamp"
Copy-Item "C:\xampp\htdocs\apsdreamhomefinal\.htaccess" "C:\xampp\htdocs\apsdreamhomefinal\.htaccess.bak_$timestamp"

# Clean Apache Configuration
$httpdContent = @"
# Comprehensive Apache Configuration
ServerRoot "C:/xampp/apache"
DocumentRoot "C:/xampp/htdocs"

# Basic Server Configuration
ServerName localhost
Listen 80

# Modules
LoadModule access_compat_module modules/mod_access_compat.so
LoadModule actions_module modules/mod_actions.so
LoadModule alias_module modules/mod_alias.so
LoadModule allowmethods_module modules/mod_allowmethods.so
LoadModule auth_basic_module modules/mod_auth_basic.so
LoadModule authn_core_module modules/mod_authn_core.so
LoadModule authn_file_module modules/mod_authn_file.so
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_groupfile_module modules/mod_authz_groupfile.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule authz_user_module modules/mod_authz_user.so
LoadModule autoindex_module modules/mod_autoindex.so
LoadModule dir_module modules/mod_dir.so
LoadModule env_module modules/mod_env.so
LoadModule filter_module modules/mod_filter.so
LoadModule headers_module modules/mod_headers.so
LoadModule log_config_module modules/mod_log_config.so
LoadModule mime_module modules/mod_mime.so
LoadModule negotiation_module modules/mod_negotiation.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule setenvif_module modules/mod_setenvif.so

# PHP Module Configuration
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
PHPIniDir "C:/xampp/php"

# PHP Handler
AddHandler application/x-httpd-php .php
AddType application/x-httpd-php .php

# Directory Configurations
<Directory />
    Options FollowSymLinks
    AllowOverride None
    Require all denied
</Directory>

<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<Directory "C:/xampp/htdocs/apsdreamhomefinal">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

# Default configuration
<IfModule dir_module>
    DirectoryIndex index.php index.html
</IfModule>
"@

$httpdContent | Set-Content "C:\xampp\apache\conf\httpd.conf"

# Update .htaccess
$htaccessContent = @"
# PHP Error Reporting
php_flag display_startup_errors on
php_flag display_errors on
php_flag html_errors on

# Error Reporting Level
php_value error_reporting -1

# Enable URL Rewriting
RewriteEngine On

# Protect Sensitive Files
<FilesMatch "^(config\.php|\.htaccess)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Default Index Files
DirectoryIndex index.php index.html

# Prevent Directory Listing
Options -Indexes

# Additional Security Headers
<IfModule headers_module>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
"@

$htaccessContent | Set-Content "C:\xampp\htdocs\apsdreamhomefinal\.htaccess"

# Restart Apache Service
net start apache2

# Verify Apache Status
$apacheStatus = net start | findstr "Apache"
Write-Host "Apache Service Status: $apacheStatus"

# Optional: Open browser to verify
Start-Process "http://localhost"
