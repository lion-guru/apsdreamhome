@echo off
echo ========================================
echo    APS DREAM HOME - XAMPP STARTER
echo ========================================
echo.

echo [1/6] Cleaning up old processes...
taskkill /f /im httpd.exe >nul 2>&1
taskkill /f /im mysqld.exe >nul 2>&1
taskkill /f /im xampp-control.exe >nul 2>&1
echo     ✅ Cleanup complete

echo.
echo [2/6] Opening XAMPP Control Panel...
cd /d C:\xampp
start xampp-control.exe
echo     ✅ XAMPP Control Panel started

echo.
echo [3/6] Please follow these steps:
echo     1. Click START button next to Apache
echo     2. Click START button next to MySQL
echo     3. Wait for both to turn GREEN
echo.

echo [4/6] Testing services...
timeout /t 15 /nobreak > nul

netstat -an 2>nul | findstr :80 >nul
if %errorlevel% equ 0 (
    echo     ✅ Apache is running on port 80
) else (
    echo     ❌ Apache failed to start
)

netstat -an 2>nul | findstr :3306 >nul
if %errorlevel% equ 0 (
    echo     ✅ MySQL is running on port 3306
) else (
    echo     ❌ MySQL failed to start
)

echo.
echo [5/6] Opening test page...
start http://localhost/apsdreamhomefinal/working-test.html
echo     ✅ Test page opened

echo.
echo ========================================
echo    SETUP COMPLETE
echo ========================================
echo.
echo Your website will be available at:
echo http://localhost/apsdreamhomefinal/index.php
echo.
echo If you see errors, please:
echo 1. Check XAMPP Control Panel
echo 2. Make sure both services are GREEN
echo 3. Clear browser cache (Ctrl+F5)
echo.
echo Press any key to close...
pause >nul
