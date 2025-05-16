# Automated Routing and Configuration Fix Script

# Function to backup existing files
function Backup-File {
    param([string]$SourcePath)
    
    if (Test-Path $SourcePath) {
        $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
        $backupPath = "$SourcePath.backup_$timestamp"
        Copy-Item $SourcePath $backupPath
        Write-Host "Backed up $SourcePath to $backupPath" -ForegroundColor Green
    }
}

# Main Project .htaccess Configuration
$mainHtaccessPath = "C:\xampp\htdocs\apsdreamhomefinal\.htaccess"
Backup-File -SourcePath $mainHtaccessPath

$mainHtaccessContent = @"
# Enable URL rewriting
RewriteEngine On

# Set base directory
RewriteBase /apsdreamhomefinal/

# Prevent directory listing
Options -Indexes

# Set default character set
AddDefaultCharset UTF-8

# Custom error page
ErrorDocument 404 /404.php

# Security headers
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options nosniff
</IfModule>

# Routing Rules
# Redirect admin access
RewriteCond %{REQUEST_URI} ^/apsdreamhomefinal/admin/ [NC]
RewriteRule ^admin/(.*)$ admin/$1 [L]

# Prevent direct access to admin from front-end
RewriteCond %{REQUEST_URI} ^/apsdreamhomefinal/admin [NC]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^admin/(.*)$ index.php [L,QSA]

# Protect sensitive directories
RewriteRule ^(admin|includes|config)/ - [F]

# Block direct access to PHP files in sensitive directories
RewriteRule ^(admin|includes|config)/.*\.php$ - [F]
"@

Set-Content -Path $mainHtaccessPath -Value $mainHtaccessContent -Encoding UTF8
Write-Host "Updated main .htaccess configuration" -ForegroundColor Green

# Admin .htaccess Configuration
$adminHtaccessPath = "C:\xampp\htdocs\apsdreamhomefinal\admin\.htaccess"
Backup-File -SourcePath $adminHtaccessPath

$adminHtaccessContent = @"
# Secure Admin Directory Configuration
Options -Indexes
RewriteEngine On
RewriteBase /apsdreamhomefinal/admin/

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

# Redirect all requests to login if not authenticated
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Prevent access from unauthorized sources
RewriteCond %{HTTP_REFERER} !^http(s)?://localhost/apsdreamhomefinal/admin/ [NC]
RewriteRule ^ - [F]
"@

Set-Content -Path $adminHtaccessPath -Value $adminHtaccessContent -Encoding UTF8
Write-Host "Updated admin .htaccess configuration" -ForegroundColor Green

# Apache Configuration Fix
$httpdConfPath = "C:\xampp\apache\conf\httpd.conf"
Backup-File -SourcePath $httpdConfPath

# Append PHP Module Configuration if not exists
$phpModuleConfig = @"

# PHP Module Configuration
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
PHPIniDir "C:/xampp/php"

# PHP Handler
AddHandler application/x-httpd-php .php
AddType application/x-httpd-php .php
"@

$httpdContent = Get-Content $httpdConfPath
if ($httpdContent -notcontains "LoadModule php_module") {
    Add-Content -Path $httpdConfPath -Value $phpModuleConfig
    Write-Host "Added PHP module configuration to httpd.conf" -ForegroundColor Green
}

# PHP Configuration Verification
$phpIniPath = "C:\xampp\php\php.ini"
Backup-File -SourcePath $phpIniPath

# Modify PHP settings
$phpIniContent = Get-Content $phpIniPath
$criticalSettings = @{
    "display_errors" = "On"
    "error_reporting" = "E_ALL"
    "session.auto_start" = "1"
}

foreach ($setting in $criticalSettings.Keys) {
    $newLine = "$setting = $($criticalSettings[$setting])"
    $phpIniContent = $phpIniContent -replace "^\s*$setting\s*=.*", $newLine
}

Set-Content -Path $phpIniPath -Value $phpIniContent -Encoding UTF8
Write-Host "Updated PHP configuration" -ForegroundColor Green

# Restart Apache (requires manual intervention)
Write-Host "`nPlease restart Apache in the XAMPP Control Panel" -ForegroundColor Yellow
Write-Host "Steps:" -ForegroundColor Cyan
Write-Host "1. Open XAMPP Control Panel" -ForegroundColor Cyan
Write-Host "2. Stop Apache" -ForegroundColor Cyan
Write-Host "3. Start Apache" -ForegroundColor Cyan

# Optional: Clear browser cache recommendation
Write-Host "`nRecommended: Clear your browser cache after restarting Apache" -ForegroundColor Magenta
