@echo off
title OpenCode API Key 1-Click Switcher
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0switch_opencode_key.ps1" %*
echo.
echo Press any key to exit...
pause >nul
