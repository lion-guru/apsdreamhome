@echo off
echo ========================================
echo    APS DREAM HOME - XAMPP STARTER
echo ========================================
echo.

echo [1/4] Stopping any existing XAMPP processes...
taskkill /f /im xampp-control.exe 2>nul
taskkill /f /im httpd.exe 2>nul
taskkill /f /im mysqld.exe 2>nul
echo     ? Cleanup complete

echo.
echo [2/4] Starting XAMPP Control Panel...
cd /d C:\xampp
start xampp-control.exe
timeout /t 4 /nobreak > nul
echo     ? XAMPP Control Panel started

echo.
echo [3/4] Please manually start these services:
echo     1. Click START button next to Apache
echo     2. Click START button next to MySQL
echo     3. Wait for both to turn GREEN
echo.

echo [4/4] Testing services...
timeout /t 10 /nobreak > nul

REM Check if services are running
netstat -an 2>nul | findstr :80 >nul
if %errorlevel% equ 0 (
    echo     ? Apache is running on port 80
) else (
    echo     ? Apache failed to start
)

netstat -an 2>nul | findstr :3306 >nul
if %errorlevel% equ 0 (
    echo     ? MySQL is running on port 3306
) else (
    echo     ? MySQL failed to start
)

echo.
echo ========================================
echo    SETUP COMPLETE
echo ========================================
echo.
echo Your website will be available at:
echo http://localhost/apsdreamhomefinal/
echo.
echo Press any key to close...
pause >nul
