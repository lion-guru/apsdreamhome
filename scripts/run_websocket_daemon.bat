@echo off
REM =====================================================================
REM APS Dream Home - WebSocket Server Runner
REM Runs websocket_server.php (port 8080) and websocket_broadcast_server.php (port 8081)
REM with auto-restart on crash, logging, and PID management.
REM =====================================================================

setlocal enabledelayedexpansion

REM Configuration
set PROJECT_ROOT=C:\xampp\htdocs\apsdreamhome
set PHP_EXE=C:\xampp\php\php.exe
set LOG_DIR=%PROJECT_ROOT%\logs\websocket
set PID_DIR=%PROJECT_ROOT%\runtime\websocket

REM Ensure directories exist
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"
if not exist "%PID_DIR%" mkdir "%PID_DIR%"

REM Server configurations
set WS_SERVER=%PROJECT_ROOT%\websocket_server.php
set WS_PORT=8080
set WS_LOG=%LOG_DIR%\websocket_server.log
set WS_PID=%PID_DIR%\websocket_server.pid

set BCAST_SERVER=%PROJECT_ROOT%\websocket_broadcast_server.php
set BCAST_PORT=8081
set BCAST_LOG=%LOG_DIR%\websocket_broadcast.log
set BCAST_PID=%PID_DIR%\websocket_broadcast.pid

REM Auto-restart settings
set MAX_RESTARTS=10
set RESTART_DELAY=5

echo [WebSocket Daemon] Starting APS Dream Home WebSocket services...
echo [WebSocket Daemon] Project root: %PROJECT_ROOT%
echo [WebSocket Daemon] PHP: %PHP_EXE%
echo [WebSocket Daemon] Logs: %LOG_DIR%
echo [WebSocket Daemon] PIDs: %PID_DIR%

REM Function to check if process is running
:check_pid
set PID_FILE=%1
if exist "%PID_FILE%" (
    for /f "tokens=*" %%a in ('type "%PID_FILE%"') do set PID=%%a
    tasklist /FI "PID eq !PID!" /FO CSV | find "!PID!" >nul
    if errorlevel 1 (
        echo [WebSocket Daemon] Stale PID file found for %PID_FILE%, removing...
        del "%PID_FILE%"
        exit /b 1
    )
    exit /b 0
) else (
    exit /b 1
)

REM Function to start a server
:start_server
set SERVER_FILE=%1
set PORT=%2
set LOG_FILE=%3
set PID_FILE=%4
set NAME=%5

call :check_pid "%PID_FILE%"
if not errorlevel 1 (
    echo [WebSocket Daemon] %NAME% already running (PID: %PID%)
    exit /b 0
)

echo [WebSocket Daemon] Starting %NAME% on port %PORT%...
cd /d "%PROJECT_ROOT%"

REM Start PHP server in background, redirect output to log
start /B "" "%PHP_EXE%" "%SERVER_FILE%" >> "%LOG_FILE%" 2>&1

REM Give it a moment to start and capture PID
timeout /t 2 /nobreak >nul

REM Find the PID of the started process
for /f "tokens=2" %%a in ('tasklist /FI "IMAGENAME eq php.exe" /FI "WINDOWTITLE eq *" /FO CSV ^| findstr /I "%SERVER_FILE:~0,-4%"') do set NEW_PID=%%a

REM Alternative: use wmic to find child process
if "!NEW_PID!"=="" (
    for /f "tokens=2 delims=," %%a in ('wmic process where "ParentProcessId=%%a and CommandLine like '%%SERVER_FILE%%'" get ProcessId /format:csv 2^>nul ^| findstr /V "ProcessId"') do set NEW_PID=%%a
)

REM Fallback: get latest php.exe
if "!NEW_PID!"=="" (
    for /f "tokens=2 delims=," %%a in ('tasklist /FI "IMAGENAME eq php.exe" /FO CSV ^| find /V "PID" ^| sort /R') do (
        set NEW_PID=%%a
        goto :got_pid
    )
)
:got_pid

if "!NEW_PID!"=="" (
    echo [WebSocket Daemon] WARNING: Could not capture PID for %NAME%
) else (
    echo !NEW_PID! > "%PID_FILE%"
    echo [WebSocket Daemon] %NAME% started with PID !NEW_PID!
)

exit /b 0

REM Function to stop a server
:stop_server
set PID_FILE=%1
set NAME=%2

if exist "%PID_FILE%" (
    for /f "tokens=*" %%a in ('type "%PID_FILE%"') do set PID=%%a
    echo [WebSocket Daemon] Stopping %NAME% (PID: %PID%)...
    taskkill /PID %PID% /T /F >nul 2>&1
    del "%PID_FILE%"
    echo [WebSocket Daemon] %NAME% stopped.
) else (
    echo [WebSocket Daemon] %NAME% not running (no PID file).
)
exit /b 0

REM Main command handling
if "%~1"=="" goto :usage
if "%~1"=="start" goto :start_all
if "%~1"=="stop" goto :stop_all
if "%~1"=="restart" goto :restart_all
if "%~1"=="status" goto :status_all
if "%~1"=="install-service" goto :install_service
if "%~1"=="uninstall-service" goto :uninstall_service

:usage
echo Usage: %0 ^<command^>
echo Commands:
echo   start           - Start both WebSocket servers
echo   stop            - Stop both WebSocket servers
echo   restart         - Restart both WebSocket servers
echo   status          - Show status of both servers
echo   install-service - Install as Windows services via NSSM
echo   uninstall-service - Uninstall Windows services
exit /b 1

:start_all
call :start_server "%WS_SERVER%" %WS_PORT% "%WS_LOG%" "%WS_PID%" "WebSocket Server (port %WS_PORT%)"
call :start_server "%BCAST_SERVER%" %BCAST_PORT% "%BCAST_LOG%" "%BCAST_PID%" "Broadcast HTTP Server (port %BCAST_PORT%)"
echo [WebSocket Daemon] All servers started.
exit /b 0

:stop_all
call :stop_server "%WS_PID%" "WebSocket Server"
call :stop_server "%BCAST_PID%" "Broadcast HTTP Server"
exit /b 0

:restart_all
call :stop_all
timeout /t 3 /nobreak >nul
call :start_all
exit /b 0

:status_all
echo [WebSocket Daemon] Status:
call :check_pid "%WS_PID%"
if not errorlevel 1 (
    echo   WebSocket Server (port %WS_PORT%): RUNNING (PID %PID%)
) else (
    echo   WebSocket Server (port %WS_PORT%): STOPPED
)
call :check_pid "%BCAST_PID%"
if not errorlevel 1 (
    echo   Broadcast HTTP Server (port %BCAST_PORT%): RUNNING (PID %PID%)
) else (
    echo   Broadcast HTTP Server (port %BCAST_PORT%): STOPPED
)
exit /b 0

:install_service
echo [WebSocket Daemon] Installing Windows services via NSSM...
REM Check if NSSM exists
where nssm >nul 2>&1
if errorlevel 1 (
    echo [WebSocket Daemon] ERROR: NSSM not found in PATH.
    echo [WebSocket Daemon] Download from https://nssm.cc/download and add to PATH.
    exit /b 1
)

echo [WebSocket Daemon] Installing APSDreamHome-WebSocket...
nssm install APSDreamHome-WebSocket "%PHP_EXE%" "%WS_SERVER%"
nssm set APSDreamHome-WebSocket AppDirectory "%PROJECT_ROOT%"
nssm set APSDreamHome-WebSocket AppStdout "%LOG_DIR%\websocket_server_service.log"
nssm set APSDreamHome-WebSocket AppStderr "%LOG_DIR%\websocket_server_service_error.log"
nssm set APSDreamHome-WebSocket AppEnvironmentExtra WS_PORT=%WS_PORT%
nssm set APSDreamHome-WebSocket Start SERVICE_AUTO_START
nssm set APSDreamHome-WebSocket Description "APS Dream Home WebSocket Server (port %WS_PORT%)"
nssm set APSDreamHome-WebSocket AppExit Default Restart
nssm set APSDreamHome-WebSocket AppThrottle 5000

echo [WebSocket Daemon] Installing APSDreamHome-WebSocket-Broadcast...
nssm install APSDreamHome-WebSocket-Broadcast "%PHP_EXE%" "%BCAST_SERVER%"
nssm set APSDreamHome-WebSocket-Broadcast AppDirectory "%PROJECT_ROOT%"
nssm set APSDreamHome-WebSocket-Broadcast AppStdout "%LOG_DIR%\websocket_broadcast_service.log"
nssm set APSDreamHome-WebSocket-Broadcast AppStderr "%LOG_DIR%\websocket_broadcast_service_error.log"
nssm set APSDreamHome-WebSocket-Broadcast AppEnvironmentExtra WS_HTTP_PORT=%BCAST_PORT%
nssm set APSDreamHome-WebSocket-Broadcast Start SERVICE_AUTO_START
nssm set APSDreamHome-WebSocket-Broadcast Description "APS Dream Home WebSocket Broadcast HTTP Server (port %BCAST_PORT%)"
nssm set APSDreamHome-WebSocket-Broadcast AppExit Default Restart
nssm set APSDreamHome-WebSocket-Broadcast AppThrottle 5000

echo [WebSocket Daemon] Services installed. Start with: nssm start APSDreamHome-WebSocket && nssm start APSDreamHome-WebSocket-Broadcast
exit /b 0

:uninstall_service
echo [WebSocket Daemon] Uninstalling Windows services...
nssm stop APSDreamHome-WebSocket >nul 2>&1
nssm remove APSDreamHome-WebSocket confirm >nul 2>&1
nssm stop APSDreamHome-WebSocket-Broadcast >nul 2>&1
nssm remove APSDreamHome-WebSocket-Broadcast confirm >nul 2>&1
echo [WebSocket Daemon] Services uninstalled.
exit /b 0