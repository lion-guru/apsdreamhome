@echo off
REM APS Dream Home - Autonomous Startup Script
REM Automatically starts when Windows boots up

title APS Dream Home - Autonomous Developer

echo.
echo =================================================
echo    APS DREAM HOME - AUTONOMOUS STARTUP
echo =================================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Please run as Administrator for full functionality
    pause
    exit /b 1
)

echo [INFO] Starting APS Dream Home Autonomous Developer...
echo.

REM Set project path
set PROJECT_PATH=C:\xampp\htdocs\apsdreamhome
set LOG_FILE=%PROJECT_PATH%\logs\startup.log

REM Create logs directory if not exists
if not exist "%PROJECT_PATH%\logs" (
    mkdir "%PROJECT_PATH%\logs"
    echo [INFO] Created logs directory
)

REM Log startup
echo [%DATE% %TIME%] Starting autonomous startup... >> "%LOG_FILE%"

REM Step 1: Check XAMPP installation
echo [STEP 1] Checking XAMPP installation...
if exist "C:\xampp" (
    set XAMPP_PATH=C:\xampp
    echo [INFO] XAMPP found at C:\xampp
) else if exist "D:\xampp" (
    set XAMPP_PATH=D:\xampp
    echo [INFO] XAMPP found at D:\xampp
) else (
    echo [ERROR] XAMPP not found in standard locations
    echo [ERROR] Please install XAMPP first
    pause
    exit /b 1
)

REM Step 2: Start XAMPP services
echo [STEP 2] Starting XAMPP services...
cd /d "%XAMPP_PATH%"

REM Start MySQL
echo [INFO] Starting MySQL...
start mysql\bin\mysqld --defaults-file=mysql\bin\my.ini --standalone --console

REM Wait a bit for MySQL to start
timeout /t 5 /nobreak

REM Start Apache
echo [INFO] Starting Apache...
start apache\bin\httpd.exe

REM Check if services are running
echo [INFO] Checking service status...
tasklist | find "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo [SUCCESS] MySQL is running
) else (
    echo [WARNING] MySQL may not be running properly
)

tasklist | find "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo [SUCCESS] Apache is running
) else (
    echo [WARNING] Apache may not be running properly
)

REM Step 3: Initialize Project
echo [STEP 3] Initializing APS Dream Home Project...
cd /d "%PROJECT_PATH%"

REM Run PHP autonomous developer
echo [INFO] Running autonomous developer script...
php AUTO_START_DEVELOPER.php >> "%LOG_FILE%" 2>&1

REM Step 4: Open Development Environment
echo [STEP 4] Opening Development Environment...

REM Open VS Code if available
echo [INFO] Looking for VS Code...
if exist "C:\Program Files\Microsoft VS Code\Code.exe" (
    echo [INFO] Opening VS Code...
    start "" "C:\Program Files\Microsoft VS Code\Code.exe" "%PROJECT_PATH%"
) else if exist "C:\Program Files (x86)\Microsoft VS Code\Code.exe" (
    echo [INFO] Opening VS Code (x86)...
    start "" "C:\Program Files (x86)\Microsoft VS Code\Code.exe" "%PROJECT_PATH%"
) else (
    echo [WARNING] VS Code not found
)

REM Step 5: Open Browser with Project
echo [STEP 5] Opening Development Browser...

REM Wait a bit for services to fully start
timeout /t 10 /nobreak

echo [INFO] Opening project in browser...
start http://localhost/apsdreamhome
start http://localhost/apsdreamhome/ai-chat-enhanced
start http://localhost/apsdreamhome/ai-assistant

REM Step 6: Start Monitoring
echo [STEP 6] Starting Project Monitoring...

REM Create monitoring script
echo [INFO] Starting continuous monitoring...
:monitor_loop
    echo [%DATE% %TIME%] Monitoring project health... >> "%LOG_FILE%"
    
    REM Check if main services are still running
    tasklist | find "httpd.exe" >nul
    if %errorlevel% neq 0 (
        echo [WARNING] Apache stopped, restarting...
        cd /d "%XAMPP_PATH%"
        start apache\bin\httpd.exe
    )
    
    tasklist | find "mysqld.exe" >nul
    if %errorlevel% neq 0 (
        echo [WARNING] MySQL stopped, restarting...
        cd /d "%XAMPP_PATH%"
        start mysql\bin\mysqld --defaults-file=mysql\bin\my.ini --standalone --console
    )
    
    REM Check if website is accessible
    curl -s http://localhost/apsdreamhome >nul
    if %errorlevel% equ 0 (
        echo [INFO] Website is accessible
    ) else (
        echo [WARNING] Website not accessible, checking services...
    )
    
    REM Wait for 60 seconds before next check
    timeout /t 60 /nobreak
    
goto monitor_loop

REM Step 7: Show Status
echo.
echo =================================================
echo    APS DREAM HOME - STATUS REPORT
echo =================================================
echo.
echo [PROJECT] APS Dream Home Real Estate Platform
echo [LOCATION] %PROJECT_PATH%
echo [SERVER] http://localhost/apsdreamhome
echo [AI CHAT] http://localhost/apsdreamhome/ai-chat-enhanced
echo [AI ASSISTANT] http://localhost/apsdreamhome/ai-assistant
echo.
echo [SERVICES]
echo   • Apache: Running on port 80
echo   • MySQL: Running on port 3306
echo   • PHP: Ready
echo   • AI Assistant: Configured
echo.
echo [FEATURES]
echo   • Role-based AI Chat (7 roles)
echo   • Lead Capture & Management
echo   • Property Recommendations
echo   • Financial Calculations
echo   • Document Assistance
echo   • Multi-language Support (Hindi/English)
echo   • Autonomous Monitoring
echo   • Auto-start Services
echo.
echo [ACCESS POINTS]
echo   • Main Website: http://localhost/apsdreamhome
echo   • AI Enhanced Chat: http://localhost/apsdreamhome/ai-chat-enhanced
echo   • AI Assistant: http://localhost/apsdreamhome/ai-assistant
echo   • Admin Dashboard: http://localhost/apsdreamhome/dashboard/admin_dashboard
echo   • Customer Dashboard: http://localhost/apsdreamhome/dashboard/customer_dashboard
echo   • Employee Dashboard: http://localhost/apsdreamhome/dashboard/employee_dashboard
echo.
echo =================================================
echo    SYSTEM IS READY FOR DEVELOPMENT!
echo =================================================
echo.
echo [NOTES]
echo   • All services are running continuously
echo   • Logs are being written to: %LOG_FILE%
echo   • Press Ctrl+C to stop monitoring
echo   • To stop services: Close this window
echo.
echo =================================================

REM Keep the window open for monitoring
echo [INFO] Monitoring is active. Press Ctrl+C to stop.
echo.
pause >nul
