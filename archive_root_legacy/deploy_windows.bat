@echo off
REM APS Dream Homes Pvt Ltd - Windows Deployment Script
REM This script helps deploy the website to a live server

echo 🚀 APS Dream Homes Pvt Ltd - Windows Deployment Script
echo ==============================================

REM Configuration
set PROJECT_NAME=apsdreamhome
set LOCAL_PATH=%~dp0
set DEPLOYMENT_FILE=%PROJECT_NAME%_deployment_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.zip

REM Colors
set GREEN=[92m
set YELLOW=[93m
set RED=[91m
set BLUE=[94m
set NC=[0m

echo.
echo Step 1: Pre-deployment checks...
echo.

REM Check if required files exist
set REQUIRED_FILES=index.php properties_template.php about_template.php contact_template.php admin_panel.php includes/db_connection.php includes/universal_template.php

for %%f in (%REQUIRED_FILES%) do (
    if exist "%%f" (
        echo ✅ %%f exists
    ) else (
        echo ❌ %%f missing
        echo Deployment failed! Missing required files.
        pause
        exit /b 1
    )
)

echo.
echo Step 2: Creating deployment package...
echo.

REM Create deployment package
powershell -Command "Compress-Archive -Path '*' -DestinationPath '../%DEPLOYMENT_FILE%' -Force"

if exist "../%DEPLOYMENT_FILE%" (
    echo ✅ Deployment package created: %DEPLOYMENT_FILE%
) else (
    echo ❌ Failed to create deployment package
    pause
    exit /b 1
)

echo.
echo Step 3: Database export...
echo.

echo Please export your database manually:
echo 1. Go to: http://localhost/phpmyadmin/
echo 2. Select database: apsdreamhome
echo 3. Click Export tab
echo 4. Choose 'Quick' export method
echo 5. Format: SQL
echo 6. Click 'Go' to download
echo.
echo Press any key when database is exported...
pause >nul

echo.
echo Step 4: Upload instructions...
echo.

echo Please upload the following file to your web hosting:
echo 📦 File: %DEPLOYMENT_FILE%
echo 📍 Location: Your web hosting public_html or www folder
echo.
echo Upload methods:
echo 1. FTP Client (FileZilla, WinSCP)
echo 2. Hosting Control Panel File Manager
echo 3. cPanel File Manager
echo.

echo Press any key when upload is complete...
pause >nul

echo.
echo Step 5: Server configuration...
echo.

echo Please complete these steps on your hosting server:
echo.
echo 1. Create a MySQL database
echo 2. Import the database SQL file
echo 3. Update database credentials in includes/db_connection.php
echo 4. Update config_production.php with your settings
echo 5. Set file permissions (755 for folders, 644 for files)
echo 6. Test the website
echo.

echo Press any key when server setup is complete...
pause >nul

echo.
echo Step 6: Final deployment checks...
echo.

echo Please verify these on your live website:
echo.
echo ✅ Homepage loads correctly
echo ✅ Properties page shows listings
echo ✅ About page displays company info
echo ✅ Contact page has forms
echo ✅ Admin panel is accessible
echo ✅ All links work properly
echo ✅ Contact information is correct
echo.

echo Press any key when all checks are complete...
pause >nul

echo.
echo ==============================================
echo 🎉 APS Dream Homes Pvt Ltd - Deployment Complete!
echo ==============================================
echo.

echo Your website is now live!
echo.
echo 📋 Next Steps:
echo 1. Set up SSL certificate (HTTPS)
echo 2. Configure professional email
echo 3. Set up Google Analytics
echo 4. Submit to Google Search Console
echo 5. Start marketing your properties
echo.

echo 📞 Contact Information:
echo Phone: +91-9554000001
echo Email: info@apsdreamhomes.com
echo.

echo 🚀 Your APS Dream Homes Pvt Ltd website is ready for business!
echo.

pause
