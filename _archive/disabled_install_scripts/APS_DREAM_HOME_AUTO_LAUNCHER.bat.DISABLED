@echo off
setlocal enabledelayedexpansion

title APS Dream Homes - Multi-Platform Auto Launcher

echo.
echo =================================================
echo    APS DREAM HOMES - FULL SYSTEM LAUNCHER
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

:: 2. Start XAMPP Services
echo [ACTION] Starting XAMPP Services (MySQL on 3307, Apache on 80)...
set XAMPP_PATH=C:\xampp

:: Start MySQL
tasklist | findstr /i "mysqld.exe" >nul
if %errorlevel% neq 0 (
    echo [INFO] Starting MySQL...
    start "" "%XAMPP_PATH%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_PATH%\mysql\bin\my.ini" --standalone
) else (
    echo [INFO] MySQL is already running.
)

:: Start Apache
tasklist | findstr /i "httpd.exe" >nul
if %errorlevel% neq 0 (
    echo [INFO] Starting Apache...
    start "" "%XAMPP_PATH%\apache\bin\httpd.exe"
) else (
    echo [INFO] Apache is already running.
)

echo [SUCCESS] XAMPP Services initialized.
echo.

:: 3. Start WSL Frappe/ERPNext
echo [ACTION] Starting Frappe/ERPNext in WSL Ubuntu...
start /min wsl -u abhay -e bash -c "cd ~ && bench start"

echo [INFO] Waiting 20 seconds for all services to initialize...
timeout /t 20

:: 4. Start External Tunnels (4 Tunnels Total)
echo [ACTION] Starting All External Tunnels (Worldwide Access)...

:: --- WSL Ubuntu Tunnels (Frappe - Port 8000) ---
echo [INFO] Starting WSL Tunnels for Frappe (Port 8000)...
:: ngrok (WSL)
start /min wsl -u abhay -e bash -c "ngrok http --url seasonless-elissa-unwrathfully.ngrok-free.dev 8000"
:: Cloudflare (WSL)
start /min wsl -u abhay -e bash -c "cloudflared tunnel --url http://localhost:8000"

:: --- Windows Tunnels (APS Dream Home - Port 80) ---
echo [INFO] Starting Windows Tunnels for APS Dream Home (Port 80)...
:: Cloudflare (Windows)
where cloudflared >nul 2>&1
if %errorlevel% equ 0 (
    echo [INFO] Starting Cloudflare Tunnel for APS Dream Home (Port 80)...
    start /min cloudflared tunnel --url http://localhost:80
) else (
    echo [WARNING] Cloudflare not found on Windows. Skipping Windows tunnel.
)
:: ngrok (Windows) - Using your second account with the static domain
start /min ngrok http --url unforced-willena-seclusively.ngrok-free.dev 80

echo.
echo ---------------------------------------------------
echo  SYSTEM IS NOW FULLY LIVE! (4 TUNNELS ACTIVE)
echo ---------------------------------------------------
echo  [FRAPPE - WSL]:
echo  • Link 1 (ngrok): bit.ly/apsdreamhome (Set to: seasonless-elissa-unwrathfully.ngrok-free.dev)
echo  • Link 2 (Cloudflare): Check WSL terminal for trycloudflare.com link
echo.
echo  [APS DREAM HOME - XAMPP]:
echo  • Link 1 (ngrok): bit.ly/apsdreamhomes (Direct: https://unforced-willena-seclusively.ngrok-free.dev/apsdreamhome/)
echo  • Link 2 (Cloudflare): Check Windows terminal for link (Add /apsdreamhome/ at the end)
echo ---------------------------------------------------
echo.
echo [INFO] Keep this window open. All tunnels are running in minimized windows.
echo [INFO] Press any key to close this launcher (Background services will continue).
pause
exit
