@echo off
REM APS Dream Home - Devin Environment Setup Script for Windows
REM This script configures environment variables and system settings

echo === APS Dream Home - Devin Environment Setup ===
echo.

REM Set project root
set PROJECT_ROOT=c:\xampp\htdocs\apsdreamhome
set APP_ENV=development
set APP_DEBUG=true

REM Database configuration
set DB_HOST=127.0.0.1
set DB_PORT=3307
set DB_NAME=apsdreamhome
set DB_USER=root
set DB_PASS=

REM Server configuration
set SERVER_HOST=localhost
set SERVER_PORT=80
set BASE_URL=http://localhost/apsdreamhome

REM PHP configuration
set PHP_MEMORY_LIMIT=256M
set PHP_MAX_EXECUTION_TIME=300

REM Application key
set APP_KEY=apsdreamhome-dev-key-2025-super-secret

REM Timezone
set APP_TIMEZONE=Asia/Kolkata

REM Display configuration
echo Environment Variables Set:
echo   PROJECT_ROOT: %PROJECT_ROOT%
echo   APP_ENV: %APP_ENV%
echo   APP_DEBUG: %APP_DEBUG%
echo   DB_HOST: %DB_HOST%
echo   DB_PORT: %DB_PORT%
echo   DB_NAME: %DB_NAME%
echo   BASE_URL: %BASE_URL%
echo   APP_TIMEZONE: %APP_TIMEZONE%
echo.

REM Check if directories exist
echo Ensuring directories exist...
if not exist "%PROJECT_ROOT%\assets\uploads" mkdir "%PROJECT_ROOT%\assets\uploads"
if not exist "%PROJECT_ROOT%\storage\logs" mkdir "%PROJECT_ROOT%\storage\logs"
if not exist "%PROJECT_ROOT%\storage\cache" mkdir "%PROJECT_ROOT%\storage\cache"
if not exist "%PROJECT_ROOT%\testing\visual_tests" mkdir "%PROJECT_ROOT%\testing\visual_tests"
echo   - Directories created/verified
echo.

REM Verify database connection
echo Testing database connection...
php -r "try { \$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', ''); echo 'Database connection successful'; } catch (PDOException \$e) { echo 'Database connection failed: ' . \$e->getMessage(); }"
echo.

echo === Environment Setup Complete ===
echo.
echo You can now start development with full permissions!
echo Devin is configured to auto-approve all operations within the project scope.
echo.

REM Set environment variables for current session
setx DB_HOST "127.0.0.1"
setx DB_PORT "3307"
setx DB_NAME "apsdreamhome"
setx DB_USER "root"
setx BASE_URL "http://localhost/apsdreamhome"
setx APP_ENV "development"

echo Environment variables have been set for this and future sessions.
echo.