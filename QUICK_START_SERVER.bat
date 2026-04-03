@echo off
setlocal enabledelayedexpansion
REM APS Dream Home - Advanced Quick Server Start
REM Optimized for XAMPP and Custom PHP environments

title APS Dream Home - Server Manager

echo.
echo =================================================
echo    APS DREAM HOME - ADVANCED SERVER START
echo =================================================
echo.

set PROJECT_PATH=C:\xampp\htdocs\apsdreamhome
set DEFAULT_PORT=8080
set SERVER_PORT=%DEFAULT_PORT%

cd /d "%PROJECT_PATH%"

:: 1. Detect PHP Path
set PHP_BIN=php
where %PHP_BIN% >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set PHP_BIN=C:\xampp\php\php.exe
        echo [INFO] Using XAMPP PHP: !PHP_BIN!
    ) else (
        echo [ERROR] PHP not found in PATH or C:\xampp\php\php.exe
        echo [ERROR] Please install PHP or XAMPP correctly.
        pause
        exit /b 1
    )
) else (
    echo [INFO] Using System PHP from PATH
)

:: 2. Check if Port is in Use
:check_port
netstat -ano | findstr ":%SERVER_PORT% " | findstr "LISTENING" >nul
if %errorlevel% equ 0 (
    echo [WARNING] Port %SERVER_PORT% is already in use.
    set /a SERVER_PORT+=1
    echo [ACTION] Trying next port: !SERVER_PORT!...
    goto check_port
)

echo [SUCCESS] Port %SERVER_PORT% is available.
echo.

:: 3. Check Configuration
if exist ".env" (
    echo [INFO] .env configuration found.
) else (
    echo [WARNING] .env file not found. System might use fallback settings.
)

:: 4. Start PHP Development Server
echo.
echo [ACTION] Starting server at http://localhost:%SERVER_PORT%...
echo [INFO] Press Ctrl+C twice to stop the server.
echo.

:: Run server and keep window open on failure
"!PHP_BIN!" -S localhost:%SERVER_PORT%

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] PHP Server failed to start or crashed.
    echo [TIP] Check if another process is blocking the port.
    echo [TIP] Check PHP error logs.
    pause
)

echo.
echo =================================================
echo    Server stopped.
echo =================================================
pause
