# Apache and PHP Configuration Verification

# Check Apache Configuration
Write-Host "🌐 Apache Configuration Check" -ForegroundColor Green

# Verify Apache Installation
$apacheExe = "C:\xampp\apache\bin\httpd.exe"
if (Test-Path $apacheExe) {
    Write-Host "Apache Executable Found: ✅" -ForegroundColor Green
    
    # Check Apache Version
    $apacheVersion = & $apacheExe -v
    Write-Host "Apache Version: $apacheVersion" -ForegroundColor Cyan
} else {
    Write-Host "Apache Executable Not Found: ❌" -ForegroundColor Red
}

# Check PHP CLI
Write-Host "`n🐘 PHP Configuration" -ForegroundColor Green
$phpVersion = & php -v
Write-Host "PHP Version: $phpVersion" -ForegroundColor Cyan

# Check PHP Modules
Write-Host "`n🔌 PHP Modules" -ForegroundColor Green
$phpModules = & php -m
$criticalModules = @('mysqli', 'pdo', 'session', 'curl', 'json')

foreach ($module in $criticalModules) {
    $status = $phpModules -contains $module
    $color = $status ? 'Green' : 'Red'
    Write-Host "$module Module: $($status ? '✅ Enabled' : '❌ Disabled')" -ForegroundColor $color
}

# Apache Virtual Hosts Configuration
Write-Host "`n🏠 Virtual Hosts Configuration" -ForegroundColor Green
$vhostsPath = "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
if (Test-Path $vhostsPath) {
    $vhostsContent = Get-Content $vhostsPath
    $vhostsContent | ForEach-Object {
        if ($_ -match 'VirtualHost') {
            Write-Host $_ -ForegroundColor Cyan
        }
    }
} else {
    Write-Host "Virtual Hosts Configuration Not Found" -ForegroundColor Yellow
}

# Pause to view results
Read-Host "Press Enter to continue..."
