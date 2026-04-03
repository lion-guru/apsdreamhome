@echo off
setlocal enabledelayedexpansion

title XAMPP - Reset to Factory Defaults (Restore Port 3306 & Root htdocs)

echo.
echo =================================================
echo    XAMPP - FACTORY RESET CONFIGURATOR
echo =================================================
echo.
echo This script will:
echo 1. Restore MySQL to default Port 3306
echo 2. Restore Apache DocumentRoot to C:/xampp/htdocs
echo 3. Reset phpMyAdmin and Control Panel UI
echo.

:: Check for Admin Rights
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Please run this script as ADMINISTRATOR!
    echo Right-click on this file and select 'Run as administrator'.
    pause
    exit /b 1
)

set APACHE_CONF=C:\xampp\apache\conf\httpd.conf
set MYSQL_INI=C:\xampp\mysql\bin\my.ini
set PMA_CONFIG=C:\xampp\phpMyAdmin\config.inc.php
set XAMPP_INI=C:\xampp\xampp-control.ini

echo [ACTION] Restoring MySQL Port to 3306...
powershell -Command "(Get-Content '%MYSQL_INI%') -replace 'port=3307', 'port=3306' | Set-Content '%MYSQL_INI%'"

echo [ACTION] Restoring Apache DocumentRoot to default htdocs...
powershell -Command "(Get-Content '%APACHE_CONF%') -replace 'DocumentRoot \"C:/xampp/htdocs/apsdreamhome\"', 'DocumentRoot \"C:/xampp/htdocs\"' -replace '<Directory \"C:/xampp/htdocs/apsdreamhome\">', '<Directory \"C:/xampp/htdocs\">' | Set-Content '%APACHE_CONF%'"

echo [ACTION] Resetting phpMyAdmin Config...
powershell -Command "$content = Get-Content '%PMA_CONFIG%'; $content = $content -replace \"'port'\] = '3307'\", \"'port'] = '3306'\"; $content = $content -replace \"'host'\\].*\", \"'host'] = '127.0.0.1';\"; $content | Set-Content '%PMA_CONFIG%'"

echo [ACTION] Resetting XAMPP Control Panel UI...
powershell -Command "(Get-Content '%XAMPP_INI%') -replace 'MySQL=3307', 'MySQL=3306' | Set-Content '%XAMPP_INI%'"

echo.
echo =================================================
echo    RESET COMPLETED SUCCESSFULLY!
echo =================================================
echo.
echo NEXT STEPS:
echo 1. QUIT XAMPP Control Panel completely.
echo 2. Open XAMPP Control Panel again.
echo 3. MySQL will now use 3306 and Apache will show all folders.
echo.
echo [IMPORTANT] Remember to stop WSL/Frappe if you use Port 3306!
echo.
pause
exit
