@echo off
echo Starting Windsurf Auto-Monitor...
echo This will run in background and auto-restart when needed.
echo.
echo Press Ctrl+C to stop monitoring
echo.

powershell -WindowStyle Hidden -ExecutionPolicy Bypass -File ".\.windsurf\auto_restart.ps1"

pause
