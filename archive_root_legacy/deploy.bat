@echo off
REM APS Dream Home - ONE-CLICK Docker Deployment
REM =============================================
REM Simple, foolproof deployment script for Windows

setlocal enabledelayedexpansion

echo 🚀 APS Dream Home - ONE-CLICK Deployment
echo =========================================
echo.

REM Set variables
set DOCKER_USER=abhaysingh3007
set DOCKER_REPO=aps-dream-home
set DOMAIN=%1

REM Check if domain is provided
if "%DOMAIN%"=="" (
    echo ❌ Error: Please provide your domain name
    echo.
    echo Usage: deploy.bat yourdomain.com
    echo Example: deploy.bat apsdreamhome.com
    echo.
    echo Or run without parameters for interactive mode:
    echo deploy.bat
    echo.
    pause
    exit /b 1
)

echo 📋 Deploying to domain: %DOMAIN%
echo.

REM Check Docker
echo 🔍 Checking Docker Desktop...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Desktop is not installed or not running.
    echo.
    echo Please:
    echo 1. Install Docker Desktop for Windows
    echo 2. Start Docker Desktop
    echo 3. Wait for it to fully start
    echo 4. Run this script again
    echo.
    echo Download: https://docs.docker.com/desktop/install/windows-install/
    echo.
    pause
    exit /b 1
) else (
    echo ✅ Docker Desktop is running
)

echo.

REM Step 1: Docker Login
echo 🔐 Step 1: Docker Hub Login
echo ===========================
echo.
echo Please enter your Docker password when prompted:
docker login -u %DOCKER_USER%
if errorlevel 1 (
    echo.
    echo ❌ Login failed. Please check your credentials.
    echo Make sure you're using the correct password/token.
    echo.
    pause
    exit /b 1
)
echo ✅ Login successful
echo.

REM Step 2: Tag Images
echo 🏷️  Step 2: Tagging Images
echo ============================
echo.
echo Tagging application image...
docker tag apsdreamhome_app %DOCKER_USER%/%DOCKER_REPO%:latest

echo Tagging database image...
docker tag apsdreamhome_mysql %DOCKER_USER%/%DOCKER_REPO%:mysql

echo Tagging web server image...
docker tag apsdreamhome_nginx %DOCKER_USER%/%DOCKER_REPO%:nginx

echo ✅ All images tagged
echo.

REM Step 3: Push Images
echo 📤 Step 3: Pushing to Docker Hub
echo =================================
echo.

echo Pushing application image...
docker push %DOCKER_USER%/%DOCKER_REPO%:latest

echo Pushing database image...
docker push %DOCKER_USER%/%DOCKER_REPO%:mysql

echo Pushing web server image...
docker push %DOCKER_USER%/%DOCKER_REPO%:nginx

echo ✅ All images pushed successfully
echo.

REM Step 4: Deploy
echo 🚀 Step 4: Production Deployment
echo ================================
echo.

REM Check if deployment script exists
if not exist "deploy_enhanced.bat" (
    echo ❌ deploy_enhanced.bat not found.
    echo Please make sure all deployment files are in place.
    echo.
    pause
    exit /b 1
)

echo Deploying for domain: %DOMAIN%
call deploy_enhanced.bat %DOMAIN%

if errorlevel 1 (
    echo.
    echo ❌ Deployment failed.
    echo Please check the error messages above.
    echo.
    echo Common fixes:
    echo - Close all PowerShell terminals and try again
    echo - Restart Docker Desktop
    echo - Check your internet connection
    echo.
    pause
    exit /b 1
)

echo.
echo 🎉 SUCCESS! Deployment completed!
echo =================================
echo.
echo 🌐 Your website is now live at: https://%DOMAIN%
echo.
echo 📊 Monitoring Dashboard: http://localhost:9090
echo 📈 Grafana Dashboard: http://localhost:3000
echo.
echo 📋 What to do next:
echo 1. Update your domain DNS to point to this server
echo 2. Get a proper SSL certificate (replace self-signed)
echo 3. Set up email notifications in .env.production
echo 4. Configure monitoring alerts
echo 5. Test all features thoroughly
echo.
echo 🔒 Security checklist:
echo - [ ] Change all default passwords
echo - [ ] Set up proper SSL certificate
echo - [ ] Configure firewall rules
echo - [ ] Enable monitoring alerts
echo - [ ] Regular backup testing
echo.
echo 🚀 Your APS Dream Home is ready for business!
echo.
echo 📞 Need help? Contact: admin@apsdreamhome.com
echo.

pause
