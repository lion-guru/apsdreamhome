@echo off
REM APS Dream Home - Manual Test Startup Script
REM Test individual components step by step

title APS Dream Home - Manual Startup Test

echo.
echo =================================================
echo    APS DREAM HOME - MANUAL STARTUP TEST
echo =================================================
echo.

set PROJECT_PATH=C:\xampp\htdocs\apsdreamhome
set XAMPP_PATH=C:\xampp

echo [INFO] Project Path: %PROJECT_PATH%
echo [INFO] XAMPP Path: %XAMPP_PATH%
echo.

REM Test Step 1: XAMPP Services
echo [TEST 1] Testing XAMPP Services...
echo.

echo Checking if XAMPP is installed...
if exist "%XAMPP_PATH%" (
    echo ✅ XAMPP directory found
) else (
    echo ❌ XAMPP directory NOT found
    pause
    exit /b 1
)

echo.
echo Checking current service status...
tasklist | find "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ MySQL is currently RUNNING
) else (
    echo ❌ MySQL is NOT running
)

tasklist | find "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ Apache is currently RUNNING
) else (
    echo ❌ Apache is NOT running
)

echo.
echo Press any key to test starting services...
pause >nul

REM Test Starting Services
echo.
echo [ACTION] Starting XAMPP services...

cd /d "%XAMPP_PATH%"

REM Start MySQL
echo Starting MySQL...
start "MySQL" /MIN mysql\bin\mysqld --defaults-file=mysql\bin\my.ini --standalone --console
timeout /t 5 /nobreak

REM Start Apache  
echo Starting Apache...
start "Apache" /MIN apache\bin\httpd.exe
timeout /t 5 /nobreak

REM Check again
echo.
echo Checking service status after start...
tasklist | find "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ MySQL started successfully
) else (
    echo ❌ MySQL failed to start
)

tasklist | find "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ Apache started successfully
) else (
    echo ❌ Apache failed to start
)

echo.
echo Press any key to test project initialization...
pause >nul

REM Test Step 2: Project Initialization
echo.
echo [TEST 2] Testing Project Initialization...

cd /d "%PROJECT_PATH%"

echo Current directory: %CD%
echo Checking for AUTO_START_DEVELOPER.php...
if exist "AUTO_START_DEVELOPER.php" (
    echo ✅ AUTO_START_DEVELOPER.php found
    echo Running PHP script...
    php AUTO_START_DEVELOPER.php
) else (
    echo ❌ AUTO_START_DEVELOPER.php NOT found
)

echo.
echo Press any key to test VS Code...
pause >nul

REM Test Step 3: VS Code
echo.
echo [TEST 3] Testing VS Code Launch...

set VSCODE_FOUND=0
if exist "C:\Program Files\Microsoft VS Code\Code.exe" (
    echo ✅ VS Code found at: C:\Program Files\Microsoft VS Code\
    echo Launching VS Code...
    start "" "C:\Program Files\Microsoft VS Code\Code.exe" "%PROJECT_PATH%"
    set VSCODE_FOUND=1
)

if exist "C:\Program Files (x86)\Microsoft VS Code\Code.exe" (
    if %VSCODE_FOUND% equ 0 (
        echo ✅ VS Code found at: C:\Program Files (x86)\Microsoft VS Code\
        echo Launching VS Code...
        start "" "C:\Program Files (x86)\Microsoft VS Code\Code.exe" "%PROJECT_PATH%"
        set VSCODE_FOUND=1
    )
)

if %VSCODE_FOUND% equ 0 (
    echo ❌ VS Code NOT found in standard locations
)

echo.
echo Press any key to test browser...
pause >nul

REM Test Step 4: Browser
echo.
echo [TEST 4] Testing Browser Launch...

echo Testing server response...
curl -s -o nul http://localhost/apsdreamhome >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Server is responding at localhost
    echo Opening browser tabs...
    start http://localhost/apsdreamhome
    timeout /t 2 /nobreak
    start http://localhost/apsdreamhome/ai-chat-enhanced
    timeout /t 2 /nobreak
    start http://localhost/apsdreamhome/ai-assistant
    echo ✅ Browser tabs opened
) else (
    echo ❌ Server is NOT responding
    echo Opening browser anyway for testing...
    start http://localhost/apsdreamhome
)

echo.
echo =================================================
echo    MANUAL TEST COMPLETED
echo =================================================
echo.
echo [SUMMARY]
echo • XAMPP Services: Tested
echo • Project Init: Tested  
echo • VS Code: Tested
echo • Browser: Tested
echo.
echo Check each component above for ❌ marks
echo.
pause
