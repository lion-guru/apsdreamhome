# Apache Configuration Repair Script
# Version 1.0

# Stop on any error
$ErrorActionPreference = 'Stop'

# Logging function
function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path "C:\xampp\apache_repair_log.txt" -Value "[$timestamp] $Message"
    Write-Host $Message
}

try {
    # 1. Backup existing configuration files
    Write-Log "Creating backup of configuration files..."
    Copy-Item "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.backup" -Force
    Copy-Item "C:\xampp\apache\conf\extra\httpd-vhosts.conf" "C:\xampp\apache\conf\extra\httpd-vhosts.conf.backup" -Force

    # 2. Repair httpd.conf file
    Write-Log "Repairing Apache configuration..."
    $httpdContent = Get-Content "C:\xampp\apache\conf\httpd.conf"
    
    # Ensure Directory configuration allows .htaccess
    $newHttpdContent = $httpdContent | ForEach-Object {
        if ($_ -match '<Directory "C:/xampp/htdocs"') {
            $inDirectoryBlock = $true
        }
        
        if ($inDirectoryBlock -and $_ -match 'AllowOverride None') {
            $_ = $_ -replace 'AllowOverride None', 'AllowOverride All'
            $inDirectoryBlock = $false
        }
        
        $_
    }
    
    Set-Content -Path "C:\xampp\apache\conf\httpd.conf" -Value $newHttpdContent

    # 3. Enable necessary Apache modules
    $modulesToEnable = @(
        'LoadModule rewrite_module modules/mod_rewrite.so',
        'LoadModule headers_module modules/mod_headers.so'
    )

    $moduleContent = Get-Content "C:\xampp\apache\conf\httpd.conf"
    $modulesToEnable | ForEach-Object {
        if ($moduleContent -notcontains $_) {
            Add-Content -Path "C:\xampp\apache\conf\httpd.conf" -Value $_
        }
    }

    # 4. Configure Virtual Hosts
    $vhostConfig = @"
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/apsdreamhomefinal"
    ServerName localhost
    <Directory "C:/xampp/htdocs/apsdreamhomefinal">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

    Set-Content -Path "C:\xampp\apache\conf\extra\httpd-vhosts.conf" -Value $vhostConfig

    # 5. Kill any existing Apache processes
    Write-Log "Terminating existing Apache processes..."
    Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force

    # 6. Restart Apache service
    Write-Log "Restarting Apache service..."
    taskkill /F /IM httpd.exe
    Start-Process "C:\xampp\apache\bin\httpd.exe"

    Write-Log "Apache configuration repair completed successfully!"
}
catch {
    Write-Log "Error during Apache repair: $_"
    Write-Log "Detailed error: $($_.Exception.Message)"
}
