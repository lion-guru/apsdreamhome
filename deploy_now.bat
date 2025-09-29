@echo off
REM APS Dream Homes Pvt Ltd - ONE-CLICK DEPLOYMENT
REM This script creates a deployment package ready for upload

echo 🚀 APS Dream Homes Pvt Ltd - ONE-CLICK DEPLOYMENT
echo ================================================
echo.

REM Configuration
set PROJECT_NAME=apsdreamhomefinal
set DEPLOYMENT_FILE=%PROJECT_NAME%_deployment_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.zip

echo Step 1: Creating deployment package...
echo.

REM Create deployment package
powershell -Command "Compress-Archive -Path '*' -DestinationPath '../%DEPLOYMENT_FILE%' -Force"

if exist "../%DEPLOYMENT_FILE%" (
    echo ✅ Deployment package created: %DEPLOYMENT_FILE%
    echo.
    echo Step 2: Database export...
    echo.
    echo Please export your database:
    echo 1. Go to: http://localhost/phpmyadmin/
    echo 2. Select database: apsdreamhomefinal
    echo 3. Click Export ^> Quick ^> Go
    echo.
    echo Press any key when database is exported...
    pause >nul

    echo.
    echo ================================================
    echo 🎉 DEPLOYMENT PACKAGE READY!
    echo ================================================
    echo.
    echo 📦 File to upload: %DEPLOYMENT_FILE%
    echo 📍 Upload location: Your hosting public_html folder
    echo.
    echo 🚀 NEXT STEPS:
    echo 1. Go to https://www.000webhost.com/
    echo 2. Create FREE account
    echo 3. Upload %DEPLOYMENT_FILE%
    echo 4. Import database_backup.sql
    echo 5. Update database credentials
    echo 6. Your site goes LIVE!
    echo.
    echo 🌐 Your website will be: https://yourname.000webhostapp.com/
    echo.
    echo 📞 Contact: +91-9554000001
    echo 📧 Email: info@apsdreamhomes.com
    echo.
    echo Press any key to open deployment guide...
    pause >nul

    REM Open deployment guide
    start INSTANT_DEPLOYMENT.md
    start https://www.000webhost.com/

) else (
    echo ❌ Failed to create deployment package
    echo Please check if all files are present
    pause
    exit /b 1
)

echo.
echo ================================================
echo ✅ ONE-CLICK DEPLOYMENT COMPLETE!
echo ================================================
echo.
echo Your APS Dream Homes Pvt Ltd website is ready for deployment!
echo.
pause
