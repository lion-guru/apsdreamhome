@echo off
REM APS Dream Home - Docker Windows Deployment Script
REM ==================================================
REM This script handles complete Docker deployment on Windows

echo 🚀 APS Dream Home - Docker Windows Deployment
echo ==============================================
echo.

REM Set variables
set DOCKER_USER=abhaysingh3007
set DOCKER_REPO=aps-dream-home
set DOMAIN=%1

REM Check if domain is provided
if "%DOMAIN%"=="" (
    echo ❌ Error: Please provide your domain name
    echo Usage: deploy_docker_windows.bat yourdomain.com
    echo Example: deploy_docker_windows.bat apsdreamhome.com
    echo.
    pause
    exit /b 1
)

echo 📋 Starting Docker deployment for domain: %DOMAIN%
echo.

REM Check Docker Desktop
echo 🔍 Checking Docker Desktop...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Desktop is not running or not installed.
    echo Please install Docker Desktop for Windows and make sure it's running.
    echo Download: https://docs.docker.com/desktop/install/windows-install/
    echo.
    pause
    exit /b 1
) else (
    echo ✅ Docker Desktop is running
)

echo.

REM Step 1: Login to Docker Hub
echo 🔐 Step 1: Logging into Docker Hub...
echo.
echo Please enter your Docker password when prompted:
docker login -u %DOCKER_USER%
if errorlevel 1 (
    echo ❌ Docker login failed. Please check your credentials.
    pause
    exit /b 1
)
echo ✅ Docker login successful
echo.

REM Step 2: Tag images
echo 🏷️  Step 2: Tagging Docker images...
echo Tagging application image...
docker tag apsdreamhome_app %DOCKER_USER%/%DOCKER_REPO%:latest

echo Tagging MySQL image...
docker tag apsdreamhome_mysql %DOCKER_USER%/%DOCKER_REPO%:mysql

echo Tagging Nginx image...
docker tag apsdreamhome_nginx %DOCKER_USER%/%DOCKER_REPO%:nginx

echo ✅ All images tagged successfully
echo.

REM Step 3: Push images to Docker Hub
echo 📤 Step 3: Pushing images to Docker Hub...
echo.

echo Pushing latest image...
docker push %DOCKER_USER%/%DOCKER_REPO%:latest

echo Pushing mysql image...
docker push %DOCKER_USER%/%DOCKER_REPO%:mysql

echo Pushing nginx image...
docker push %DOCKER_USER%/%DOCKER_REPO%:nginx

echo ✅ All images pushed successfully
echo.

REM Step 4: Deploy to production
echo 🚀 Step 4: Deploying to production...
echo.
echo Deploying for domain: %DOMAIN%
echo.

REM Check if deploy_enhanced.bat exists
if not exist "deploy_enhanced.bat" (
    echo ❌ deploy_enhanced.bat not found.
    echo Please make sure deploy_enhanced.bat exists in the current directory.
    pause
    exit /b 1
)

REM Run the enhanced deployment script
call deploy_enhanced.bat %DOMAIN%

if errorlevel 1 (
    echo ❌ Deployment failed. Check the error messages above.
) else (
    echo ✅ Deployment completed successfully!
    echo.
    echo 🌐 Your application should be available at: https://%DOMAIN%
    echo 📊 Monitoring: http://localhost:9090
    echo 📈 Dashboard: http://localhost:3000
)

echo.
echo 📋 Next Steps:
echo 1. Update your DNS to point %DOMAIN% to this server
echo 2. Configure SSL certificate for production
echo 3. Set up monitoring alerts
echo 4. Test all functionality
echo.
echo 🎉 Docker deployment process completed!
echo.
echo 🔧 Troubleshooting:
echo - If you get PowerShell errors, close all terminals and open a new one
echo - Make sure Docker Desktop is running
echo - Check that all images are built correctly
echo.
pause
