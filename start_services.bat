@echo off
REM ============================================================
REM APS Dream Home - Startup Script
REM Run this after computer restart to start all services
REM ============================================================

echo ============================================================
echo APS Dream Home - Startup Script
echo ============================================================

REM Step 1: Kill any stuck processes
echo [1/5] Killing stuck processes...
taskkill /F /IM httpd.exe 2>nul
taskkill /F /IM mysqld.exe 2>nul
taskkill /F /IM php.exe 2>nul
timeout /t 3 /nobreak >nul

REM Step 2: Delete stuck PID file
echo [2/5] Cleaning PID file...
del /F "C:\xampp\apache\logs\httpd.pid" 2>nul

REM Step 3: Start MySQL
echo [3/5] Starting MySQL...
start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"
timeout /t 5 /nobreak >nul

REM Step 4: Start Apache
echo [4/5] Starting Apache...
start "" "C:\xampp\apache\bin\httpd.exe"
timeout /t 8 /nobreak >nul

REM Step 5: Verify services
echo [5/5] Verifying services...
echo.

REM Check MySQL
mysql -h 127.0.0.1 -P 3307 -u root -e "SELECT 1" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] MySQL is running
) else (
    echo [FAIL] MySQL is NOT running
)

REM Check Apache
powershell -Command "try { $r = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/' -UseBasicParsing -TimeoutSec 5; Write-Output '[OK] Apache is running (' + $r.StatusCode + ')' } catch { Write-Output '[FAIL] Apache is NOT running' }"

echo.
echo ============================================================
echo Startup complete!
echo Open browser: http://localhost/apsdreamhome/
echo ============================================================
pause
