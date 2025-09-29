# Fix PHP Apache Configuration

# Path to httpd.conf
$httpdConfPath = "C:\xampp\apache\conf\httpd.conf"

# PHP Module Configuration
$phpModuleConfig = @"

# PHP Module Configuration
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
PHPIniDir "C:/xampp/php"

# PHP Handler
AddHandler application/x-httpd-php .php
AddType application/x-httpd-php .php
"@

# Check if configuration already exists
$httpdContent = Get-Content $httpdConfPath
if ($httpdContent -notcontains "LoadModule php_module") {
    # Append PHP configuration
    Add-Content -Path $httpdConfPath -Value $phpModuleConfig
    Write-Host "PHP module configuration added to httpd.conf"
} else {
    Write-Host "PHP module configuration already exists"
}

# Verify PHP CLI version
& php -v

# Restart Apache (you'll need to do this manually in XAMPP Control Panel)
Write-Host "Please restart Apache in the XAMPP Control Panel"
