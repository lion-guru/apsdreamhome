@echo off
REM APS Dream Home - Git Auto Sync Batch Script
REM Automatically synchronizes Git changes between local and remote

echo Starting Git Auto-Sync...
echo.

REM Change to project directory
cd /d "C:\xampp\htdocs\apsdreamhome"

REM Run the PHP sync script
php git_auto_sync.php

echo.
echo Git Auto-Sync completed.
pause
