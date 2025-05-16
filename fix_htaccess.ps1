# Fix .htaccess Configuration Script

$htaccessPath = "C:\xampp\htdocs\apsdreamhomefinal\.htaccess"
$phpIniPath = "C:\xampp\php\php.ini"

# Backup existing .htaccess
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item $htaccessPath "$htaccessPath.backup_$timestamp"

# Create new .htaccess content
$newHtaccessContent = @"
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
"@

# Write new .htaccess
Set-Content -Path $htaccessPath -Value $newHtaccessContent

# Update php.ini with the configuration previously in .htaccess
$phpIniContent = Get-Content $phpIniPath

# PHP Configuration Updates
$phpConfigUpdates = @{
    "display_errors" = "Off"
    "error_reporting" = "E_ALL"
    "session.cookie_httponly" = "1"
    "session.use_only_cookies" = "1"
    "session.cookie_secure" = "0"
    "session.gc_maxlifetime" = "1800"
    "max_execution_time" = "300"
    "upload_max_filesize" = "10M"
    "post_max_size" = "10M"
    "opcache.enable" = "1"
    "opcache.memory_consumption" = "128"
    "opcache.interned_strings_buffer" = "8"
    "opcache.max_accelerated_files" = "4000"
    "opcache.validate_timestamps" = "1"
    "short_open_tag" = "On"
}

# Apply PHP configuration updates
foreach ($setting in $phpConfigUpdates.GetEnumerator()) {
    $key = $setting.Key
    $value = $setting.Value
    
    # Find and replace or append the setting
    $found = $false
    for ($i = 0; $i -lt $phpIniContent.Count; $i++) {
        if ($phpIniContent[$i] -match "^\s*$key\s*=") {
            $phpIniContent[$i] = "$key = $value"
            $found = $true
            break
        }
    }
    
    # If not found, append to the end
    if (-not $found) {
        $phpIniContent += "$key = $value"
    }
}

# Write updated php.ini
Set-Content -Path $phpIniPath -Value $phpIniContent

Write-Host "Configuration updated successfully!"
