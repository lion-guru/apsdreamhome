@echo off
setlocal enabledelayedexpansion

echo =================================================
echo    APS DREAM HOME - XAMPP PORT FIXER (3307)
echo =================================================
echo.
echo This script will change XAMPP MySQL port from 3306 to 3307
echo to avoid conflict with WSL (Frappe).
echo.

:: Check for Admin Rights
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Please run this script as ADMINISTRATOR!
    echo Right-click on this file and select 'Run as administrator'.
    pause
    exit /b 1
)

set MYSQL_INI=C:\xampp\mysql\bin\my.ini
set PMA_CONFIG=C:\xampp\phpMyAdmin\config.inc.php
set XAMPP_INI=C:\xampp\xampp-control.ini
set APACHE_CONF=C:\xampp\apache\conf\httpd.conf

:: 1. Update my.ini
:: ... existing my.ini code ...

:: 2. Update Apache DocumentRoot (Direct Site Access)
if exist "%APACHE_CONF%" (
    echo [ACTION] Updating Apache DocumentRoot to point directly to apsdreamhome...
    powershell -Command "(Get-Content '%APACHE_CONF%') -replace 'DocumentRoot \"C:/xampp/htdocs\"', 'DocumentRoot \"C:/xampp/htdocs/apsdreamhome\"' -replace '<Directory \"C:/xampp/htdocs\">', '<Directory \"C:/xampp/htdocs/apsdreamhome\">' | Set-Content '%APACHE_CONF%'"
    echo [SUCCESS] Apache now points directly to your project.
)

:: 3. Update xampp-control.ini (To fix UI errors)
if exist "%XAMPP_INI%" (
    echo [ACTION] Updating %XAMPP_INI%...
    powershell -Command "(Get-Content '%XAMPP_INI%') -replace 'MySQL=3306', 'MySQL=3307' | Set-Content '%XAMPP_INI%'"
    echo [SUCCESS] XAMPP Control Panel configured to look for port 3307
) else (
    echo [ERROR] %XAMPP_INI% not found!
)

:: 3. Update phpMyAdmin config
if exist "%PMA_CONFIG%" (
    echo [ACTION] Updating %PMA_CONFIG%...
    :: Use a more robust powershell script to fix the syntax error and set port correctly
    powershell -Command "$content = Get-Content '%PMA_CONFIG%'; $content = $content -replace \"'host'\\].*\", \"'host'] = '127.0.0.1';\"; if ($content -notmatch \"'port'\") { $content = $content -replace \"'host'\] = '127.0.0.1';\", \"'host'] = '127.0.0.1';`n`$cfg['Servers'][`$i]['port'] = '3307';\" } else { $content = $content -replace \"'port'\] = '.*'\", \"'port'] = '3307'\" }; $content | Set-Content '%PMA_CONFIG%'"
    echo [SUCCESS] phpMyAdmin configured to use port 3307
) else (
    echo [ERROR] %PMA_CONFIG% not found!
)

echo.
echo =================================================
echo    FIX COMPLETED SUCCESSFULLY!
echo =================================================
echo.
echo NEXT STEPS:
echo 1. Open XAMPP Control Panel.
echo 2. Stop MySQL (if running).
echo 3. Start MySQL again.
echo 4. Access phpMyAdmin at http://localhost/phpmyadmin/
echo.
pause
