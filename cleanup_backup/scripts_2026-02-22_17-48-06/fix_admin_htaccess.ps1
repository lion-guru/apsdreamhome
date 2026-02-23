# Fix Admin .htaccess Configuration Script

$adminHtaccessPath = "C:\xampp\htdocs\apsdreamhome\admin\.htaccess"

# Backup existing .htaccess
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item $adminHtaccessPath "$adminHtaccessPath.backup_$timestamp"

# Create new .htaccess content
$newHtaccessContent = @"
# Secure Admin Directory Configuration

# Disable directory browsing
Options -Indexes

# Enable URL rewriting
RewriteEngine On
RewriteBase /apsdreamhome/admin/

# Protect sensitive files
<FilesMatch "^(\.htaccess|\.htpasswd|config\.php|database\.php|\.env)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Block access to PHP files except specific admin pages
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

<FilesMatch "^(index\.php|login\.php|dashboard\.php|admin_login_handler\.php)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Security Headers
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header always append X-Frame-Options SAMEORIGIN
    Header set X-Content-Type-Options nosniff
</IfModule>

# Redirect all requests to login if not authenticated
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Error Documents
ErrorDocument 403 /admin/403.php
ErrorDocument 404 /admin/404.php
"@

# Write the new .htaccess content
$newHtaccessContent | Set-Content $adminHtaccessPath -Encoding UTF8

Write-Host "Successfully updated admin .htaccess configuration."
Write-Host "Backup created at: $adminHtaccessPath.backup_$timestamp"
