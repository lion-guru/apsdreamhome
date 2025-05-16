# Comprehensive Apache Diagnostic and Repair Script
# Version 3.0

$ErrorActionPreference = 'Stop'

# Logging function
function Write-DiagnosticLog {
    param([string]$Message, [string]$LogLevel = 'INFO')
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$LogLevel] [$timestamp] $Message"
    Add-Content -Path "C:\xampp\apache_comprehensive_diagnostic.log" -Value $logMessage
    Write-Host $logMessage
}

try {
    # 1. Diagnostic Information Gathering
    Write-DiagnosticLog "Starting Apache Comprehensive Diagnostic"

    # Check Apache Configuration Files
    Write-DiagnosticLog "Checking Apache Configuration Files"
    $configFiles = @(
        "C:\xampp\apache\conf\httpd.conf",
        "C:\xampp\apache\conf\extra\httpd-vhosts.conf",
        "C:\xampp\htdocs\apsdreamhomefinal\admin\.htaccess"
    )

    foreach ($file in $configFiles) {
        if (Test-Path $file) {
            Write-DiagnosticLog "Analyzing $file" -LogLevel "DEBUG"
        } else {
            Write-DiagnosticLog "Configuration file not found: $file" -LogLevel "WARNING"
        }
    }

    # 2. Comprehensive Configuration Repair
    Write-DiagnosticLog "Initiating Comprehensive Configuration Repair"

    # Backup existing configurations
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    Copy-Item "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.backup_$timestamp" -Force
    Copy-Item "C:\xampp\apache\conf\extra\httpd-vhosts.conf" "C:\xampp\apache\conf\extra\httpd-vhosts.conf.backup_$timestamp" -Force

    # 3. Create Comprehensive Apache Configuration
    $comprehensiveConfig = @"
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

# Global Configuration
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

<Directory "C:/xampp/htdocs/apsdreamhomefinal/admin">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

# Virtual Hosts Configuration
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot "C:/xampp/htdocs/apsdreamhomefinal"
    ServerName localhost
    
    <Directory "C:/xampp/htdocs/apsdreamhomefinal">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Logging Configuration
ErrorLog "logs/error.log"
LogLevel warn

# Prevent Directory Browsing
<IfModule autoindex_module>
    Options -Indexes
</IfModule>

# Redirect Loop Prevention
LimitInternalRecursion 20
"@

    # Write comprehensive configuration
    Set-Content -Path "C:\xampp\apache\conf\httpd.conf" -Value $comprehensiveConfig

    # 4. Create Admin .htaccess with Specific Rules
    $adminHtaccess = @"
# Admin Directory .htaccess
RewriteEngine On
Options -Indexes

# Redirect to login if not authenticated
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ login.php [L,QSA]

# Prevent direct access to sensitive files
<FilesMatch "^(config\.php|\.htaccess)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
"@

    Set-Content -Path "C:\xampp\htdocs\apsdreamhomefinal\admin\.htaccess" -Value $adminHtaccess

    # 5. Terminate Apache Processes
    Write-DiagnosticLog "Terminating Apache Processes"
    Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force

    # 6. Restart Apache
    Write-DiagnosticLog "Restarting Apache"
    Start-Process "C:\xampp\apache\bin\httpd.exe"

    Write-DiagnosticLog "Comprehensive Apache Diagnostic and Repair Completed Successfully" -LogLevel "SUCCESS"
}
catch {
    Write-DiagnosticLog "Critical Error: $_" -LogLevel "ERROR"
    Write-DiagnosticLog "Detailed Error: $($_.Exception.Message)" -LogLevel "ERROR"
}
