@echo off
echo 🔧 APS Dream Home - Dependencies Installation Script
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
echo 📋 Installing Composer dependencies...

REM Navigate to project directory
cd /d "%~dp0"

REM Check if composer is available
composer --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Composer found, installing dependencies...
    composer install --no-dev --optimize-autoloader
    if %errorLevel% == 0 (
        echo ✅ Dependencies installed successfully!
    ) else (
        echo ❌ Composer install failed
        pause
        exit /b
    )
) else (
    echo ❌ Composer not found, downloading...
    
    REM Download Composer installer
    echo 📥 Downloading Composer installer...
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    
    REM Install Composer
    echo 🔧 Installing Composer...
    php composer-setup.php
    if %errorLevel% == 0 (
        echo ✅ Composer installed successfully!
        
        REM Install dependencies
        echo 📦 Installing dependencies...
        php composer.phar install --no-dev --optimize-autoloader
        if %errorLevel% == 0 (
            echo ✅ Dependencies installed successfully!
        ) else (
            echo ❌ Dependencies installation failed
            pause
            exit /b
        )
    ) else (
        echo ❌ Composer installation failed
        pause
        exit /b
    )
    
    REM Cleanup
    if exist composer-setup.php del composer-setup.php
)

echo.
echo 📋 Verifying installation...

REM Check vendor directory
if exist vendor\ (
    echo ✅ Vendor directory created
    dir vendor\ | find "File(s)" >nul
    if %errorLevel% == 0 (
        echo ✅ Vendor files exist
    ) else (
        echo ❌ Vendor directory is empty
    )
) else (
    echo ❌ Vendor directory not found
)

REM Check autoloader
if exist vendor\autoload.php (
    echo ✅ Autoloader created
) else (
    echo ❌ Autoloader not found
)

echo.
echo 🧪 Testing application...

REM Test application loading
echo 🌐 Testing application at http://localhost/apsdreamhome/public/index.php
timeout /t 3 /nobreak >nul

REM Test diagnostic
echo 🔧 Running diagnostic test...
start "" "http://localhost/apsdreamhome/diagnostic_test.php"

echo.
echo ✅ Dependencies installation complete!
echo 📊 Please check the diagnostic test results
echo 🌐 Application should now be accessible
echo.

pause
