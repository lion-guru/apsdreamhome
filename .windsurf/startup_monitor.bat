@echo off
echo Starting Windsurf Auto-Monitor in Background...
echo This will automatically restart Windsurf when it hangs.
echo.

:: Check if already running
tasklist /FI "WINDOWTITLE eq Auto-Monitor*" 2>NUL | find /I "powershell.exe" >NUL
if %ERRORLEVEL% EQU 0 (
    echo Auto-monitor is already running!
    echo To stop: Close PowerShell window or run taskkill /F /IM powershell.exe
    pause
    exit /b
)

:: Start monitoring in background
start /MIN powershell -WindowStyle Hidden -ExecutionPolicy Bypass -File ".\.windsurf\auto_restart.ps1"

echo Auto-monitor started in background!
echo It will automatically restart Windsurf when needed.
echo.
echo To stop monitoring:
echo 1. Close PowerShell window from Task Manager
echo 2. Or run: taskkill /F /IM powershell.exe
echo.
pause
