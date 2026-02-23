# Fix Apache Configuration
$httpdConfPath = "C:\xampp\apache\conf\httpd.conf"
$content = Get-Content $httpdConfPath

# Remove duplicate PHP module configurations
$cleanedContent = $content | Select-String -Pattern "LoadModule php_module" -NotMatch | 
                  Select-String -Pattern "PHPIniDir" -NotMatch | 
                  Select-String -Pattern "AddHandler application/x-httpd-php" -NotMatch | 
                  Select-String -Pattern "AddType application/x-httpd-php" -NotMatch

# Add unique PHP configuration
$cleanedContent += @"

# PHP Module Configuration
LoadModule php_module "C:/xampp/php/php8apache2_4.dll"
PHPIniDir "C:/xampp/php"

# PHP Handler
AddHandler application/x-httpd-php .php
AddType application/x-httpd-php .php
"@

# Write back the cleaned configuration
$cleanedContent | Set-Content $httpdConfPath

# Restart Apache
net stop apache2
net start apache2
