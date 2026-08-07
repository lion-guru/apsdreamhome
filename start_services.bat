@echo off
REM ============================================================
REM APS Dream Home - Startup Script
REM Run this after computer restart to start all services
REM ============================================================
REM Prerequisites: Apache httpd.conf has:
REM   - ServerName localhost:80
REM   - ServerRoot "C:/xampp/apache"
REM   - DocumentRoot "C:/xampp/htdocs"
REM   - LoadModule rewrite_module (enabled)
REM   - Include conf/extra/httpd-mpm.conf (ThreadsPerChild 300)
REM   - Include conf/extra/httpd-xampp.conf (PHP module loading)
REM ============================================================

echo ============================================================
echo APS Dream Home - Startup Script
echo MySQL max_connections = 500 (set in my.ini)
echo Virtual Host: apsdreamhome.local
echo ============================================================

REM Step 1: Kill any stuck processes
echo [1/6] Killing stuck processes...
taskkill /F /IM httpd.exe 2>nul
taskkill /F /IM mysqld.exe 2>nul
taskkill /F /IM php.exe 2>nul
timeout /t 3 /nobreak >nul

REM Step 2: Clean PID files
echo [2/6] Cleaning PID files...
del /F "C:\xampp\apache\logs\httpd.pid" 2>nul
del /F "C:\xampp\mysql\data\*.pid" 2>nul

REM Step 3: Verify Apache configuration
echo [3/6] Verifying Apache configuration...
"C:\xampp\apache\bin\httpd.exe" -t >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [FAIL] Apache configuration error. Run httpd.exe -t manually to diagnose.
    pause
    exit /b 1
)
echo [OK] Apache config syntax valid

REM Step 4: Start MySQL
echo [4/6] Starting MySQL...
start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"
timeout /t 5 /nobreak >nul

REM Step 5: Start Apache
echo [5/6] Starting Apache...
start "" "C:\xampp\apache\bin\httpd.exe"
timeout /t 8 /nobreak >nul

REM Step 6: Verify services
echo [6/6] Verifying services...
echo.

REM Check MySQL
"C:\xampp\mysql\bin\mysql.exe" -h 127.0.0.1 -P 3307 -u root -e "SELECT 1" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] MySQL is running on port 3307
) else (
    echo [FAIL] MySQL is NOT running
)

REM Check Apache
powershell -Command "$r = Invoke-WebRequest -Uri 'http://localhost/apsdreamhome/' -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop; $s = $r.StatusCode; echo \"[OK] Apache is running (HTTP $s)\"" 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Apache is running on port 80
) else (
    echo [FAIL] Apache is NOT running
)

echo.
echo ============================================================
echo Startup complete!
echo Open browser: http://localhost/apsdreamhome/
echo Admin Login:  http://localhost/apsdreamhome/admin/login?test_login=1
echo ============================================================
pause
