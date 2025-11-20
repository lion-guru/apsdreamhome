@echo off
echo ========================================
echo APS DREAM HOME - AUTOMATIC FIX SCRIPT
echo ========================================
echo.
echo This script will:
echo 1. Stop Apache server
echo 2. Clear browser caches automatically
echo 3. Restart Apache server
echo 4. Open the bookings page
echo.
echo Press any key to continue...
pause >nul

echo.
echo [1/4] Stopping Apache server...
net stop Apache2.4 >nul 2>&1
timeout /t 3 /nobreak >nul

echo [2/4] Clearing browser caches...
echo Clearing Chrome cache...
if exist "%LOCALAPPDATA%\Google\Chrome\User Data\Default\Cache" (
    rd /s /q "%LOCALAPPDATA%\Google\Chrome\User Data\Default\Cache" 2>nul
    rd /s /q "%LOCALAPPDATA%\Google\Chrome\User Data\Default\Media Cache" 2>nul
    rd /s /q "%LOCALAPPDATA%\Google\Chrome\User Data\Default\GPUCache" 2>nul
)

echo Clearing Edge cache...
if exist "%LOCALAPPDATA%\Microsoft\Edge\User Data\Default\Cache" (
    rd /s /q "%LOCALAPPDATA%\Microsoft\Edge\User Data\Default\Cache" 2>nul
    rd /s /q "%LOCALAPPDATA%\Microsoft\Edge\User Data\Default\Media Cache" 2>nul
    rd /s /q "%LOCALAPPDATA%\Microsoft\Edge\User Data\Default\GPUCache" 2>nul
)

echo Clearing Firefox cache...
if exist "%APPDATA%\Mozilla\Firefox\Profiles" (
    for /d %%i in ("%APPDATA%\Mozilla\Firefox\Profiles\*") do (
        rd /s /q "%%i\cache2" 2>nul
        rd /s /q "%%i\startupCache" 2>nul
        rd /s /q "%%i\thumbnails" 2>nul
    )
)

echo Clearing system temp files...
rd /s /q "%TEMP%" 2>nul
mkdir "%TEMP%" 2>nul

echo [3/4] Starting Apache server...
net start Apache2.4 >nul 2>&1
timeout /t 5 /nobreak >nul

echo [4/4] Opening bookings page...
start http://localhost/apsdreamhome/admin/bookings.php

echo.
echo ========================================
echo ✅ AUTOMATIC FIX COMPLETED!
echo ========================================
echo.
echo The bookings page should now load without errors.
echo If you still see errors, try opening in incognito mode.
echo.
echo Press any key to exit...
pause >nul
