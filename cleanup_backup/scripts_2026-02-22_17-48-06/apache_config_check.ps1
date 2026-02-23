# Apache Configuration Diagnostic Script

# Check Apache Configuration
Write-Host "Apache Configuration Check" -ForegroundColor Green

# Check Apache Modules
Write-Host "`nEnabled Apache Modules:" -ForegroundColor Cyan
& "C:\xampp\apache\bin\httpd.exe" -M | Where-Object { $_ -like "*_module*" }

# Check Virtual Hosts Configuration
Write-Host "`nVirtual Hosts Configuration:" -ForegroundColor Cyan
$vhostsPath = "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
if (Test-Path $vhostsPath) {
    Get-Content $vhostsPath | Select-String -Pattern "VirtualHost"
} else {
    Write-Host "Virtual Hosts configuration file not found." -ForegroundColor Yellow
}

# Check Main Apache Configuration
Write-Host "`nMain Apache Configuration:" -ForegroundColor Cyan
$apacheConfPath = "C:\xampp\apache\conf\httpd.conf"
if (Test-Path $apacheConfPath) {
    $content = Get-Content $apacheConfPath
    $content | Select-String -Pattern "DocumentRoot|ServerName|RewriteEngine"
} else {
    Write-Host "Apache configuration file not found." -ForegroundColor Yellow
}

# Check Rewrite Module
Write-Host "`nRewrite Module Status:" -ForegroundColor Cyan
$rewriteModule = & "C:\xampp\apache\bin\httpd.exe" -M | Where-Object { $_ -like "*rewrite_module*" }
if ($rewriteModule) {
    Write-Host "Rewrite Module is ENABLED" -ForegroundColor Green
} else {
    Write-Host "Rewrite Module is DISABLED" -ForegroundColor Red
}

# Check PHP Module
Write-Host "`nPHP Module Status:" -ForegroundColor Cyan
$phpModule = & "C:\xampp\apache\bin\httpd.exe" -M | Where-Object { $_ -like "*php*" }
if ($phpModule) {
    Write-Host "PHP Module is ENABLED" -ForegroundColor Green
} else {
    Write-Host "PHP Module is DISABLED" -ForegroundColor Red
}

# Pause to view results
Read-Host "Press Enter to continue..."
