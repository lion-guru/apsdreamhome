@echo off
echo ========================================
echo APS DREAM HOME - AUTONOMOUS TRIGGER
echo ========================================
echo.
echo Starting Super Admin Autonomous System...
echo.

REM Check if PHP is available
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not available in PATH
    echo Please install PHP or add it to PATH
    pause
    exit /b 1
)

REM Change to project directory
cd /d "%~dp0"

REM Create logs directory if not exists
if not exist "logs" mkdir logs

REM Start autonomous trigger system
echo Starting autonomous monitoring system...
echo Press Ctrl+C to stop
echo.

php app/Core/Autonomous/TriggerSystem.php

pause
