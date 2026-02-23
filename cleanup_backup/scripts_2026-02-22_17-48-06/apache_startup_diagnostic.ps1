# Apache Startup Diagnostic and Repair Script

# Function to log diagnostic information
function Write-DiagnosticLog {
    param([string]$Message)
    $logPath = "C:\xampp\htdocs\apsdreamhome\apache_diagnostic_log.txt"
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "$timestamp - $Message" | Out-File -Append -FilePath $logPath
}

# Start logging
Write-DiagnosticLog "Starting Apache Diagnostic Script"

# Check Apache Executable
$apacheExe = "C:\xampp\apache\bin\httpd.exe"
if (-not (Test-Path $apacheExe)) {
    Write-DiagnosticLog "ERROR: Apache executable not found"
    Write-Host "Apache executable not found at $apacheExe" -ForegroundColor Red
    exit 1
}

# Check for Port Conflicts
function Test-PortAvailability {
    param([int]$Port)
    $tcpConnection = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue
    return ($tcpConnection -eq $null)
}

# Common Apache Ports
$ports = @(80, 443, 8080)
$blockedPorts = $ports | Where-Object { -not (Test-PortAvailability $_) }

if ($blockedPorts) {
    Write-DiagnosticLog "Blocked Ports Detected: $($blockedPorts -join ', ')"
    Write-Host "Blocked Ports: $($blockedPorts -join ', ')" -ForegroundColor Yellow
    
    # Attempt to kill processes using these ports
    foreach ($port in $blockedPorts) {
        $process = Get-NetTCPConnection -LocalPort $port | Select-Object -ExpandProperty OwningProcess
        if ($process) {
            Stop-Process -Id $process -Force
            Write-DiagnosticLog "Stopped process using port $port"
        }
    }
}

# Check Apache Configuration Files
$configFiles = @(
    "C:\xampp\apache\conf\httpd.conf",
    "C:\xampp\apache\conf\extra\httpd-vhosts.conf",
    "C:\xampp\htdocs\apsdreamhome\.htaccess",
    "C:\xampp\htdocs\apsdreamhome\admin\.htaccess"
)

foreach ($file in $configFiles) {
    if (Test-Path $file) {
        $content = Get-Content $file
        $syntaxErrors = $content | Where-Object { $_ -match 'Invalid|Error|Syntax' }
        
        if ($syntaxErrors) {
            Write-DiagnosticLog "Potential configuration error in $file"
            Write-Host "Potential configuration error in $file" -ForegroundColor Yellow
            $syntaxErrors | ForEach-Object { Write-DiagnosticLog $_ }
        }
    } else {
        Write-DiagnosticLog "Configuration file not found: $file"
    }
}

# Check PHP Configuration
$phpIniPath = "C:\xampp\php\php.ini"
if (Test-Path $phpIniPath) {
    $phpContent = Get-Content $phpIniPath
    $phpErrors = $phpContent | Where-Object { $_ -match 'error|warning' }
    
    if ($phpErrors) {
        Write-DiagnosticLog "Potential PHP configuration issues"
        Write-Host "Potential PHP configuration issues" -ForegroundColor Yellow
    }
}

# Repair Apache Configuration
$apacheConfPath = "C:\xampp\apache\conf\httpd.conf"
$apacheConfBackup = "$apacheConfPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $apacheConfPath $apacheConfBackup

# Add essential configuration if missing
$essentialConfig = @"

# Essential Apache Configuration
ServerName localhost
DocumentRoot "C:/xampp/htdocs"

# PHP Module Configuration
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
PHPIniDir "C:/xampp/php"

# Enable URL Rewriting
LoadModule rewrite_module modules/mod_rewrite.so

# Security and Performance
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
"@

$currentConfig = Get-Content $apacheConfPath
if ($currentConfig -notcontains "LoadModule php_module") {
    Add-Content -Path $apacheConfPath -Value $essentialConfig
    Write-DiagnosticLog "Added essential configuration to httpd.conf"
}

# Final Diagnostic Output
Write-Host "Apache Diagnostic Script Completed" -ForegroundColor Green
Write-DiagnosticLog "Diagnostic Script Completed"

# Recommendation
Write-Host "`nRecommended Actions:" -ForegroundColor Cyan
Write-Host "1. Check the diagnostic log at C:\xampp\htdocs\apsdreamhome\apache_diagnostic_log.txt" -ForegroundColor Cyan
Write-Host "2. Manually review Apache and PHP configuration files" -ForegroundColor Cyan
Write-Host "3. Restart XAMPP Control Panel" -ForegroundColor Cyan
