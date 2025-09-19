@echo off
echo ==============================================
echo   APS Dream Home - Test Environment Setup
echo ==============================================
echo.

echo [1/4] Checking PHP installation...
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PHP is not installed or not in PATH
    pause
    exit /b 1
)

echo [2/4] Checking MySQL server...
sc query MySQL80 >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ MySQL service is not running. Please start MySQL server.
    pause
    exit /b 1
)

echo [3/4] Creating test database and tables...
php init_test_db.php
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Failed to initialize test database
    pause
    exit /b 1
)

echo [4/4] Setting up environment variables...
echo # Copy these to your .env file > .env.example
echo DB_HOST=localhost >> .env.example
echo DB_NAME=aps_dream_home >> .env.example
echo DB_USER=root >> .env.example
echo DB_PASS= >> .env.example
echo API_BASE_URL=http://localhost/apsdreamhomefinal/api/v1 >> .env.example
echo API_DEBUG=true >> .env.example
echo JWT_SECRET=your_jwt_secret_key_here >> .env.example
echo.

echo ==============================================
echo   Test Environment Setup Complete!
echo ==============================================
echo.
echo ✅ Test database has been initialized
echo ✅ .env.example file has been created
echo.
echo Next steps:
echo 1. Copy .env.example to .env
echo 2. Update the values in .env if needed
echo 3. Run 'run_tests.bat' to run all tests
echo.
pause
