@echo off
REM APS Dream Home - Quick Server Start
REM Simple PHP development server starter

title APS Dream Home - Quick Server Start

echo.
echo =================================================
echo    APS DREAM HOME - QUICK SERVER START
echo =================================================
echo.

set PROJECT_PATH=C:\xampp\htdocs\apsdreamhome

echo [INFO] Project: %PROJECT_PATH%
echo [INFO] Starting PHP development server...
echo.

cd /d "%PROJECT_PATH%"

REM Check if .env exists and has AI config
if exist ".env" (
    echo [INFO] Configuration file found
    findstr /C:"AI_API_KEY=" .env >nul
    if %errorlevel% equ 0 (
        echo [SUCCESS] AI API Key configured
    ) else (
        echo [WARNING] AI API Key not configured
    )
) else (
    echo [WARNING] .env file not found
)

REM Start PHP server
echo [ACTION] Starting PHP development server...
php -S localhost:8000

if %errorlevel% equ 0 (
    echo [SUCCESS] Server started successfully
    echo.
    echo [ACCESS URLS]
    echo   • Main Site: http://localhost:8000
    echo   • AI Chat: http://localhost:8000/ai-chat-enhanced
    echo   • AI Assistant: http://localhost:8000/ai-assistant
    echo   • Admin: http://localhost:8000/dashboard/admin_dashboard
    echo.
    echo [INFO] Press Ctrl+C to stop server
    echo.
) else (
    echo [ERROR] Failed to start server
    echo [INFO] Check PHP installation
    echo [INFO] Check port 8000 availability
    pause
)

echo.
echo =================================================
echo    Server is running - Press Ctrl+C to stop
echo =================================================

REM Keep server running
:loop
timeout /t 60 /nobreak
goto loop
