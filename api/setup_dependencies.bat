@echo off
echo ==============================================
echo   APS Dream Home API - Setup Dependencies
echo ==============================================
echo.

echo [1/3] Checking Node.js and npm...
node --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Node.js is not installed. Please install it from https://nodejs.org/
    pause
    exit /b 1
)

npm --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ npm is not installed. Please install Node.js from https://nodejs.org/
    pause
    exit /b 1
)

echo [2/3] Checking Python...
python --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Python is not installed. Please install it from https://www.python.org/downloads/
    pause
    exit /b 1
)

echo [3/3] Installing required npm packages...
cd %~dp0
npm install node-fetch@^2.6.7 --save >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Failed to install npm packages
    pause
    exit /b 1
)

echo.
echo ==============================================
echo   Setup completed successfully!
echo   You can now run the tests using run_tests.bat
echo ==============================================
pause
