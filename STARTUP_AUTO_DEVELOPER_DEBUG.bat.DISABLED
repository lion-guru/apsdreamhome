@echo off
REM APS Dream Home - Autonomous Startup Script (DEBUG VERSION)
REM Enhanced with better error handling and logging

title APS Dream Home - Autonomous Developer (DEBUG)

echo.
echo =================================================
echo    APS DREAM HOME - AUTONOMOUS STARTUP (DEBUG)
echo =================================================
echo.

REM Create logs directory if not exists
set PROJECT_PATH=C:\xampp\htdocs\apsdreamhome
set LOG_FILE=%PROJECT_PATH%\logs\startup_debug.log
set ERROR_LOG=%PROJECT_PATH%\logs\startup_errors.log

if not exist "%PROJECT_PATH%\logs" (
    mkdir "%PROJECT_PATH%\logs"
    echo [INFO] Created logs directory
)

REM Log startup with timestamp
echo [%DATE% %TIME%] Starting DEBUG autonomous startup... >> "%LOG_FILE%"
echo [%DATE% %TIME%] Current directory: %CD% >> "%LOG_FILE%"

REM Check if running as administrator
echo [STEP 0] Checking administrator privileges...
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [WARNING] Not running as administrator - some features may not work
    echo [%DATE% %TIME%] WARNING: Not running as administrator >> "%LOG_FILE%"
) else (
    echo [SUCCESS] Running as administrator
    echo [%DATE% %TIME%] SUCCESS: Running as administrator >> "%LOG_FILE%"
)

REM Step 1: Check XAMPP installation with detailed logging
echo [STEP 1] Checking XAMPP installation...
echo [%DATE% %TIME%] Checking XAMPP paths... >> "%LOG_FILE%"

if exist "C:\xampp" (
    set XAMPP_PATH=C:\xampp
    echo [INFO] XAMPP found at C:\xampp
    echo [%DATE% %TIME%] XAMPP found at C:\xampp >> "%LOG_FILE%"
) else if exist "D:\xampp" (
    set XAMPP_PATH=D:\xampp
    echo [INFO] XAMPP found at D:\xampp
    echo [%DATE% %TIME%] XAMPP found at D:\xampp >> "%LOG_FILE%"
) else (
    echo [ERROR] XAMPP not found in standard locations
    echo [%DATE% %TIME%] ERROR: XAMPP not found >> "%ERROR_LOG%"
    echo [%DATE% %TIME%] Checked C:\xampp and D:\xampp - neither found >> "%ERROR_LOG%"
    pause
    exit /b 1
)

REM Step 2: Start XAMPP services with enhanced error handling
echo [STEP 2] Starting XAMPP services...
echo [%DATE% %TIME%] Changing to XAMPP directory: %XAMPP_PATH% >> "%LOG_FILE%"

cd /d "%XAMPP_PATH%"
if %errorlevel% neq 0 (
    echo [ERROR] Cannot change to XAMPP directory
    echo [%DATE% %TIME%] ERROR: Cannot change to %XAMPP_PATH% >> "%ERROR_LOG%"
    pause
    exit /b 1
)

REM Start MySQL with better error checking
echo [INFO] Starting MySQL...
echo [%DATE% %TIME%] Starting MySQL service... >> "%LOG_FILE%"

REM Check if MySQL already running
tasklist | find "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo [INFO] MySQL is already running
    echo [%DATE% %TIME%] INFO: MySQL already running >> "%LOG_FILE%"
) else (
    echo [%DATE% %TIME%] Starting new MySQL instance... >> "%LOG_FILE%"
    start "MySQL Server" /MIN mysql\bin\mysqld --defaults-file=mysql\bin\my.ini --standalone --console
    
    REM Wait and check if started
    timeout /t 10 /nobreak
    tasklist | find "mysqld.exe" >nul
    if %errorlevel% equ 0 (
        echo [SUCCESS] MySQL started successfully
        echo [%DATE% %TIME%] SUCCESS: MySQL started >> "%LOG_FILE%"
    ) else (
        echo [ERROR] MySQL failed to start
        echo [%DATE% %TIME%] ERROR: MySQL failed to start >> "%ERROR_LOG%"
    )
)

REM Start Apache with better error checking
echo [INFO] Starting Apache...
echo [%DATE% %TIME%] Starting Apache service... >> "%LOG_FILE%"

REM Check if Apache already running
tasklist | find "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo [INFO] Apache is already running
    echo [%DATE% %TIME%] INFO: Apache already running >> "%LOG_FILE%"
) else (
    echo [%DATE% %TIME%] Starting new Apache instance... >> "%LOG_FILE%"
    start "Apache Server" /MIN apache\bin\httpd.exe
    
    REM Wait and check if started
    timeout /t 10 /nobreak
    tasklist | find "httpd.exe" >nul
    if %errorlevel% equ 0 (
        echo [SUCCESS] Apache started successfully
        echo [%DATE% %TIME%] SUCCESS: Apache started >> "%LOG_FILE%"
    ) else (
        echo [ERROR] Apache failed to start
        echo [%DATE% %TIME%] ERROR: Apache failed to start >> "%ERROR_LOG%"
    )
)

REM Step 3: Initialize Project with error handling
echo [STEP 3] Initializing APS Dream Home Project...
echo [%DATE% %TIME%] Changing to project directory... >> "%LOG_FILE%"

cd /d "%PROJECT_PATH%"
if %errorlevel% neq 0 (
    echo [ERROR] Cannot change to project directory
    echo [%DATE% %TIME%] ERROR: Cannot change to %PROJECT_PATH% >> "%ERROR_LOG%"
    pause
    exit /b 1
)

REM Run PHP autonomous developer with error capture
echo [INFO] Running autonomous developer script...
echo [%DATE% %TIME%] Running AUTO_START_DEVELOPER.php... >> "%LOG_FILE%"

if exist "AUTO_START_DEVELOPER.php" (
    php AUTO_START_DEVELOPER.php >> "%LOG_FILE%" 2>&1
    if %errorlevel% neq 0 (
        echo [WARNING] PHP script had some issues
        echo [%DATE% %TIME%] WARNING: PHP script exit code %errorlevel% >> "%ERROR_LOG%"
    ) else (
        echo [SUCCESS] PHP script completed successfully
        echo [%DATE% %TIME%] SUCCESS: PHP script completed >> "%LOG_FILE%"
    )
) else (
    echo [ERROR] AUTO_START_DEVELOPER.php not found
    echo [%DATE% %TIME%] ERROR: AUTO_START_DEVELOPER.php not found >> "%ERROR_LOG%"
)

REM Step 4: Open Development Environment
echo [STEP 4] Opening Development Environment...
echo [%DATE% %TIME%] Looking for VS Code... >> "%LOG_FILE%"

REM Check multiple VS Code locations
set VSCODE_FOUND=0

if exist "C:\Program Files\Microsoft VS Code\Code.exe" (
    echo [INFO] Found VS Code at C:\Program Files\Microsoft VS Code\
    echo [%DATE% %TIME%] Found VS Code at Program Files >> "%LOG_FILE%"
    start "" "C:\Program Files\Microsoft VS Code\Code.exe" "%PROJECT_PATH%"
    set VSCODE_FOUND=1
) 

if exist "C:\Program Files (x86)\Microsoft VS Code\Code.exe" (
    echo [INFO] Found VS Code at C:\Program Files (x86)\Microsoft VS Code\
    echo [%DATE% %TIME%] Found VS Code at Program Files (x86) >> "%LOG_FILE%"
    if %VSCODE_FOUND% equ 0 (
        start "" "C:\Program Files (x86)\Microsoft VS Code\Code.exe" "%PROJECT_PATH%"
        set VSCODE_FOUND=1
    )
) 

if %VSCODE_FOUND% equ 0 (
    echo [WARNING] VS Code not found in standard locations
    echo [%DATE% %TIME%] WARNING: VS Code not found >> "%ERROR_LOG%"
)

REM Step 5: Open Browser with Project
echo [STEP 5] Opening Development Browser...
echo [%DATE% %TIME%] Waiting for services to fully start... >> "%LOG_FILE%"

REM Wait longer for services to fully start
timeout /t 15 /nobreak

echo [INFO] Opening project in browser...
echo [%DATE% %TIME%] Opening browser tabs... >> "%LOG_FILE%"

REM Test if server is responding before opening browser
curl -s -o nul http://localhost/apsdreamhome >nul 2>&1
if %errorlevel% equ 0 (
    echo [SUCCESS] Server is responding
    echo [%DATE% %TIME%] SUCCESS: Server responding at localhost >> "%LOG_FILE%"
    
    start http://localhost/apsdreamhome
    timeout /t 3 /nobreak
    start http://localhost/apsdreamhome/ai-chat-enhanced
    timeout /t 3 /nobreak
    start http://localhost/apsdreamhome/ai-assistant
    echo [%DATE% %TIME%] Browser tabs opened >> "%LOG_FILE%"
) else (
    echo [WARNING] Server not responding, opening anyway
    echo [%DATE% %TIME%] WARNING: Server not responding >> "%ERROR_LOG%"
    start http://localhost/apsdreamhome
    start http://localhost/apsdreamhome/ai-chat-enhanced
    start http://localhost/apsdreamhome/ai-assistant
)

REM Step 6: Show Status
echo.
echo =================================================
echo    APS DREAM HOME - DEBUG STATUS REPORT
echo =================================================
echo.
echo [PROJECT] APS Dream Home Real Estate Platform
echo [LOCATION] %PROJECT_PATH%
echo [SERVER] http://localhost/apsdreamhome
echo [LOG FILE] %LOG_FILE%
echo [ERROR LOG] %ERROR_LOG%
echo.
echo [SERVICES STATUS]
tasklist | find "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo   • MySQL: ✅ RUNNING
) else (
    echo   • MySQL: ❌ NOT RUNNING
)

tasklist | find "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo   • Apache: ✅ RUNNING
) else (
    echo   • Apache: ❌ NOT RUNNING
)
echo.
echo [DEBUG INFO]
echo   • Logs written to: %LOG_FILE%
echo   • Errors written to: %ERROR_LOG%
echo   • Current time: %TIME% %DATE%
echo.
echo =================================================
echo    DEBUG MODE - CHECK LOGS FOR DETAILS
echo =================================================
echo.
echo [NOTES]
echo   • Check startup_debug.log for detailed operation log
echo   • Check startup_errors.log for any errors
echo   • Services may take time to fully start
echo   • Browser tabs opened regardless of server response
echo.
pause
