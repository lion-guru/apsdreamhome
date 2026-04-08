@echo off
echo ====================================
echo APS DREAM HOME - SERVER STARTUP
echo ====================================
echo.

echo [1/4] Checking XAMPP Services...
echo.

REM Check if XAMPP is installed
if exist "C:\xampp\xampp-control.exe" (
    echo XAMPP Found: C:\xampp\
) else (
    echo ERROR: XAMPP not found in C:\xampp\
    echo Please install XAMPP first
    pause
    exit /b 1
)

echo.
echo [2/4] Starting Apache Service...
"C:\xampp\apache\bin\httpd.exe" -k start >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo Apache: STARTED
) else (
    echo Apache: FAILED to start
    echo Trying alternative method...
    "C:\xampp\xampp-control.exe" >nul 2>&1
)

echo.
echo [3/4] Starting MySQL Service...
"C:\xampp\mysql\bin\mysqld.exe" --console --standalone >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo MySQL: STARTED
) else (
    echo MySQL: FAILED to start
    echo Trying XAMPP Control Panel...
)

echo.
echo [4/4] Testing Connection...
echo.

REM Test if server is accessible
curl -s http://localhost/apsdreamhome/testing/server_check.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo Server Connection: SUCCESS
    echo.
    echo Opening project in browser...
    start http://localhost/apsdreamhome
) else (
    echo Server Connection: FAILED
    echo.
    echo Opening XAMPP Control Panel...
    start "C:\xampp\xampp-control.exe"
)

echo.
echo ====================================
echo SERVER STARTUP COMPLETE
echo ====================================
echo.
echo If server is not running:
echo 1. Open XAMPP Control Panel
echo 2. Start Apache and MySQL services
echo 3. Try accessing: http://localhost/apsdreamhome
echo.
echo Press any key to open XAMPP Control Panel...
pause >nul
start "C:\xampp\xampp-control.exe"
