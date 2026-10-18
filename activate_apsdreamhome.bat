@echo off
title Switch to APS Dream Home (OpenCode)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0switch_opencode_key.ps1" aps
echo.
timeout /t 3
