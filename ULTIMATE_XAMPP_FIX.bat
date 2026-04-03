
@echo off
setlocal enabledelayedexpansion

echo =================================================
echo    APS DREAM HOME - ULTIMATE FIX (DIRECT ACCESS)
echo =================================================
echo.

:: Check for Admin Rights
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Please run this script as ADMINISTRATOR!
    pause
    exit /b 1
)

set APACHE_CONF=C:\xampp\apache\conf\httpd.conf
set MYSQL_INI=C:\xampp\mysql\bin\my.ini
set PMA_CONFIG=C:\xampp\phpMyAdmin\config.inc.php
set XAMPP_INI=C:\xampp\xampp-control.ini

:: 1. Fix MySQL Port
echo [ACTION] Setting MySQL to Port 3307...
powershell -Command "(Get-Content '%MYSQL_INI%') -replace 'port=3306', 'port=3307' | Set-Content '%MYSQL_INI%'"

:: 2. Fix Apache DocumentRoot (THE JUGAAD)
echo [ACTION] Pointing Apache directly to apsdreamhome folder...
powershell -Command "(Get-Content '%APACHE_CONF%') -replace 'DocumentRoot \"C:/xampp/htdocs\"', 'DocumentRoot \"C:/xampp/htdocs/apsdreamhome\"' -replace '<Directory \"C:/xampp/htdocs\">', '<Directory \"C:/xampp/htdocs/apsdreamhome\">' | Set-Content '%APACHE_CONF%'"

:: 3. Fix phpMyAdmin Port
echo [ACTION] Configuring phpMyAdmin for Port 3307...
powershell -Command "$content = Get-Content '%PMA_CONFIG%'; $content = $content -replace \"'host'\\].*\", \"'host'] = '127.0.0.1';\"; if ($content -notmatch \"'port'\") { $content = $content -replace \"'host'\] = '127.0.0.1';\", \"'host'] = '127.0.0.1';`n`$cfg['Servers'][`$i]['port'] = '3307';\" } else { $content = $content -replace \"'port'\] = '.*'\", \"'port'] = '3307'\" }; $content | Set-Content '%PMA_CONFIG%'"

:: 4. Fix XAMPP Control Panel UI
echo [ACTION] Updating XAMPP Control Panel Settings...
powershell -Command "(Get-Content '%XAMPP_INI%') -replace 'MySQL=3306', 'MySQL=3307' | Set-Content '%XAMPP_INI%'"

echo.
echo [SUCCESS] ALL FIXES APPLIED!
echo.
echo NEXT STEPS:
echo 1. QUIT XAMPP Control Panel completely.
echo 2. Open XAMPP Control Panel again.
echo 3. Start Apache and MySQL.
echo 4. Now visit: https://unforced-willena-seclusively.ngrok-free.dev
echo    (No need to add /apsdreamhome/ anymore!)
echo.
pause
