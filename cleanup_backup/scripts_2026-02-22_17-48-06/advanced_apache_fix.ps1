# Advanced Apache Configuration Repair Script
# Version 2.0

$ErrorActionPreference = 'Stop'

function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path "C:\xampp\apache_advanced_repair_log.txt" -Value "[$timestamp] $Message"
    Write-Host $Message
}

try {
    # 1. Backup existing configuration files
    Write-Log "Creating comprehensive backups..."
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    Copy-Item "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.backup_$timestamp" -Force
    Copy-Item "C:\xampp\apache\conf\extra\httpd-vhosts.conf" "C:\xampp\apache\conf\extra\httpd-vhosts.conf.backup_$timestamp" -Force

    # 2. Read current httpd.conf
    $httpdContent = Get-Content "C:\xampp\apache\conf\httpd.conf"

    # 3. Modify configuration to allow .htaccess and fix directory settings
    $newHttpdContent = @()
    $inDirectoryBlock = $false
    $directoryConfigured = $false

    foreach ($line in $httpdContent) {
        if ($line -match '<Directory "C:/xampp/htdocs"') {
            $inDirectoryBlock = $true
        }

        if ($inDirectoryBlock) {
            if ($line -match 'AllowOverride') {
                $line = '    AllowOverride All'
            }
            if ($line -match 'Require') {
                $line = '    Require all granted'
            }
        }

        if ($line -match '</Directory>') {
            $inDirectoryBlock = $false
            $directoryConfigured = $true
        }

        $newHttpdContent += $line
    }

    # Add specific configuration for admin directory if not already present
    if (-not $directoryConfigured) {
        $newHttpdContent += '<Directory "C:/xampp/htdocs/apsdreamhome/admin">'
        $newHttpdContent += '    Options Indexes FollowSymLinks'
        $newHttpdContent += '    AllowOverride All'
        $newHttpdContent += '    Require all granted'
        $newHttpdContent += '</Directory>'
    }

    # 4. Enable critical modules
    $modulesToEnable = @(
        'LoadModule rewrite_module modules/mod_rewrite.so',
        'LoadModule headers_module modules/mod_headers.so',
        'LoadModule dir_module modules/mod_dir.so'
    )

    $modulesToEnable | ForEach-Object {
        if ($newHttpdContent -notcontains $_) {
            $newHttpdContent += $_
        }
    }

    # 5. Add global configuration to prevent redirect loops
    $newHttpdContent += 'LimitInternalRecursion 20'
    $newHttpdContent += 'LogLevel warn'

    # 6. Write modified configuration
    Set-Content -Path "C:\xampp\apache\conf\httpd.conf" -Value $newHttpdContent

    # 7. Update Virtual Hosts configuration
    $vhostConfig = @"
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/apsdreamhome"
    ServerName localhost
    <Directory "C:/xampp/htdocs/apsdreamhome">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    <Directory "C:/xampp/htdocs/apsdreamhome/admin">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

    Set-Content -Path "C:\xampp\apache\conf\extra\httpd-vhosts.conf" -Value $vhostConfig

    # 8. Terminate Apache processes
    Write-Log "Terminating Apache processes..."
    Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force

    # 9. Restart Apache
    Write-Log "Restarting Apache..."
    Start-Process "C:\xampp\apache\bin\httpd.exe"

    Write-Log "Advanced Apache configuration repair completed successfully!"
}
catch {
    Write-Log "Critical error during advanced Apache repair: $_"
    Write-Log "Detailed error: $($_.Exception.Message)"
}
