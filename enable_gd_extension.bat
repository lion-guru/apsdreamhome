@echo off
echo 🔧 APS Dream Home - GD Extension Auto-Enable Script
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Running as administrator
) else (
    echo ⚠️ Please run this script as administrator
    echo Right-click the script and select "Run as administrator"
    pause
    exit /b
)

echo.
echo 📋 Searching for XAMPP installation...
set "xampp_path="
for %%d in (C D E F G H I J K L M N O P Q R S T U V W X Y Z) do (
    if exist "%%d:\xampp\php\php.ini" (
        set "xampp_path=%%d:\xampp"
        echo ✅ Found XAMPP at: %%d:\xampp
        goto :found
    )
)

if not defined xampp_path (
    echo ❌ XAMPP not found in standard locations
    echo Please install XAMPP or specify custom path
    set /p xampp_path="Enter XAMPP path (e.g., C:\xampp): "
    if not exist "%xampp_path%\php\php.ini" (
        echo ❌ PHP.ini not found at specified path
        pause
        exit /b
    )
)

:found
echo.
echo 📝 Current PHP.ini location: %xampp_path%\php\php.ini

REM Backup original php.ini
echo 📋 Creating backup of php.ini...
copy "%xampp_path%\php\php.ini" "%xampp_path%\php\php.ini.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%-%time:~0,2%%time:~3,2%%time:~6,2%" >nul 2>&1

REM Check if GD extension is already enabled
findstr /i "extension=gd" "%xampp_path%\php\php.ini" >nul
if %errorLevel% == 0 (
    echo ✅ GD extension found in php.ini
    echo 🔍 Checking if it's enabled...
    findstr /i ";extension=gd" "%xampp_path%\php\php.ini" >nul
    if %errorLevel% == 0 (
        echo ❌ GD extension is commented out
        echo 🔧 Enabling GD extension...
        powershell -Command "(Get-Content '%xampp_path%\php\php.ini') -replace ';extension=gd', 'extension=gd' | Set-Content '%xampp_path%\php\php.ini'"
        echo ✅ GD extension enabled
    ) else (
        echo ✅ GD extension is already enabled
        echo 🧪 Testing GD extension...
        goto :test_gd
    )
) else (
    echo ❌ GD extension not found in php.ini
    echo 🔧 Adding GD extension...
    echo extension=gd >> "%xampp_path%\php\php.ini"
    echo ✅ GD extension added
)

echo.
echo 🔄 Restarting Apache service...

REM Stop Apache
echo ⏹️ Stopping Apache...
cd /d "%xampp_path%"
apache\bin\httpd.exe -k stop >nul 2>&1
timeout /t 3 /nobreak >nul

REM Start Apache
echo ▶️ Starting Apache...
apache\bin\httpd.exe -k start >nul 2>&1
timeout /t 3 /nobreak >nul

:test_gd
echo.
echo 🧪 Testing GD extension...
cd /d "%xampp_path%\php"
php -m | findstr /i gd >nul
if %errorLevel% == 0 (
    echo ✅ GD Extension is LOADED and WORKING!
    echo 🎉 SUCCESS: GD extension enabled successfully!
    echo.
    echo 📊 GD Extension Information:
    php -r "if (extension_loaded('gd')) { \$gd = gd_info(); echo 'GD Version: ' . \$gd['GD Version'] . PHP_EOL; echo 'Supported Formats: ' . implode(', ', \$gd['GD Supported Formats']) . PHP_EOL; }"
) else (
    echo ❌ GD Extension failed to load
    echo 🔍 Checking for issues...
    echo.
    echo 📋 Troubleshooting steps:
    echo 1. Check if Apache is running
    echo 2. Verify php.ini syntax
    echo 3. Check PHP error logs
    echo 4. Restart XAMPP completely
    echo.
    echo 📁 PHP Error Log: %xampp_path%\apache\logs\error.log
    echo 📁 PHP.ini: %xampp_path%\php\php.ini
)

echo.
echo 🔄 Running deployment verification...
echo 🌐 Opening verification page...
start "" "http://localhost/apsdreamhome/verify_deployment.php"

echo.
echo ✅ GD Extension Auto-Enable Script Complete!
echo 📊 Expected Result: 100% deployment success
echo 🧪 Please check the verification page for results
echo.

pause
