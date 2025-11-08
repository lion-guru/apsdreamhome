@echo off
REM APS Dream Home - Railway Deployment Helper for Windows
REM =====================================================

echo 🚀 APS Dream Home - Railway Deployment Helper
echo =============================================
echo.

REM Check if Railway CLI is installed
railway --version >nul 2>&1
if %errorlevel% neq 0 (
    echo 📦 Railway CLI not found. Installing...
    echo.
    echo Please install Railway CLI manually:
    echo 1. Go to https://docs.railway.app/develop/cli
    echo 2. Download railway.exe for Windows
    echo 3. Add to PATH or place in project folder
    echo.
    echo Or use web deployment at https://railway.app
    echo.
    pause
    exit /b 1
)

echo ✅ Railway CLI found!

REM Check if logged in
railway status >nul 2>&1
if %errorlevel% neq 0 (
    echo 🔐 Please login to Railway first:
    echo Run: railway login
    echo Then press any key to continue...
    pause
)

echo.
echo 📋 RAILWAY DEPLOYMENT CHECKLIST:
echo ================================
echo.
echo □ 1. Go to https://railway.app
echo □ 2. Create New Project
echo □ 3. Choose "Deploy from GitHub" or "Deploy from Docker"
echo □ 4. Select your APS Dream Home repository
echo □ 5. Add MySQL database service
echo □ 6. Configure environment variables from .env.railway
echo □ 7. Add custom domain: apsdreamhomes.com
echo □ 8. Click Deploy!
echo.

echo 🔧 CONFIGURATION REMINDERS:
echo ===========================
echo • Runtime: PHP 8.2
echo • Build Command: composer install ^&^& npm run build
echo • Start Command: php-fpm
echo • Port: 8080
echo.

echo 📁 IMPORTANT FILES CREATED:
echo ===========================
if exist "railway.toml" echo ✅ railway.toml - Railway configuration
if exist "Dockerfile.railway" echo ✅ Dockerfile.railway - Optimized Docker setup
if exist ".env.railway" echo ✅ .env.railway - Environment variables template
if exist "RAILWAY_DEPLOYMENT.md" echo ✅ RAILWAY_DEPLOYMENT.md - Complete deployment guide
echo.

echo 🌐 DOMAIN SETUP (GoDaddy):
echo =========================
echo After deployment, configure DNS:
echo A Record: @ -> [Railway IP]
echo CNAME: www -> [Railway Domain]
echo.

echo 🎯 POST-DEPLOYMENT CHECKLIST:
echo ============================
echo □ Website loads correctly
echo □ Database connection working
echo □ Admin panel accessible
echo □ Custom domain configured
echo □ SSL certificate active
echo.

echo 🏆 YOUR SITE WILL BE LIVE AT:
echo =============================
echo Railway URL: https://your-app.railway.app
echo Custom Domain: https://apsdreamhomes.com
echo.

echo 💡 Need help? Check RAILWAY_DEPLOYMENT.md
echo.

echo Press any key to open Railway in browser...
pause >nul
start https://railway.app

echo.
echo 🎉 Happy deploying!
echo.
