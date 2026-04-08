@echo off
setlocal enabledelayedexpansion

title APS Dream Homes - Setup Auto Startup

echo.
echo =================================================
echo    APS DREAM HOMES - STARTUP CONFIGURATOR
echo =================================================
echo. 

:: 1. Check for Administrator Rights
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Please run this script as ADMINISTRATOR!
    echo Right-click on this file and select 'Run as administrator'.
    pause
    exit /b 1
)

set SCRIPT_PATH=c:\xampp\htdocs\apsdreamhome\APS_DREAM_HOME_AUTO_LAUNCHER.bat
set TASK_NAME=APSDreamHomeLauncher

:: 2. Check if the launcher script exists
if not exist "%SCRIPT_PATH%" (
    echo [ERROR] Launcher script not found at %SCRIPT_PATH%
    pause
    exit /b 1
)

:: 3. Create Task in Task Scheduler
echo [ACTION] Creating a Scheduled Task to run at Logon with Highest Privileges...

:: Delete existing task if it exists
schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1

:: Create new task
:: /RL HIGHEST means Run as Administrator
:: /SC ONLOGON means Run when any user logs in
schtasks /create /tn "%TASK_NAME%" /tr "\"%SCRIPT_PATH%\"" /sc onlogon /rl highest /f

if %errorlevel% equ 0 (
    echo.
    echo [SUCCESS] Auto Startup has been configured!
    echo [INFO] Your system will now launch automatically when you log in.
    echo [INFO] It will run as Administrator without asking for permission.
    echo.
) else (
    echo.
    echo [ERROR] Failed to create the scheduled task.
)

pause
exit
