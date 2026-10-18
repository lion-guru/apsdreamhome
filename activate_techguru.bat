@echo off
title Switch to Techguru (OpenCode)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0switch_opencode_key.ps1" techguru
echo.
timeout /t 3
