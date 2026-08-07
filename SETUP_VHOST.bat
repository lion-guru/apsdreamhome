@echo off
echo ============================================
echo  APS Dream Home - Virtual Host Setup
echo  Run this as ADMINISTRATOR
echo ============================================
echo.

REM Add hosts entry for apsdreamhome.local
echo Checking hosts file...
findstr /C:"apsdreamhome.local" "%SystemRoot%\System32\drivers\etc\hosts" >nul 2>&1
if %errorlevel%==0 (
    echo [OK] apsdreamhome.local already in hosts file.
) else (
    echo 127.0.0.1   apsdreamhome.local >> "%SystemRoot%\System32\drivers\etc\hosts"
    echo [ADDED] apsdreamhome.local to hosts file.
)

echo.
echo ============================================
echo Done! Now restart Apache in XAMPP Control Panel.
echo Then open: http://apsdreamhome.local/
echo ============================================
echo.
pause
